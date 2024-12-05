<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  vimeoapi
 *
 * @copyright   (C) 2024 Mike Economou
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This plugin integrates Vimeo API functionalities into Joomla.
 * It allows users to easily embed Vimeo videos, retrieve video information,
 * and manage video settings directly from the Joomla interface or the shortcode.
 * 
 * Features:
 * - Embed Vimeo videos using shortcodes
 * - Fetch video details like title, description, and thumbnail
 * - Manage video settings from the Joomla admin panel or from the shortcode
 * - Uses joomla templating allowing to add or override templates
 */

namespace My\Plugin\Content\Vimeoapi\Extension;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Log\Log;

class VimeoApi extends CMSPlugin implements SubscriberInterface
{
    // set the output variable to be visible in the class
    private $videoOutput = '';
    private $position = '';

    public static function getSubscribedEvents(): array
    {
        
        return [
                'onContentPrepare' => 'replaceShortcodes',  
                'onContentAfterTitle' => 'displayAfterTitle',  
                'onContentBeforeDisplay' => 'displayBeforeContent',
                'onContentAfterDisplay' => 'displayAfterContent',
                ];
    }

    // this will be called whenever the onContentPrepare event is triggered
    public function replaceShortcodes(Event $event)
    {
        /* This function processes the text of an article being presented on the site.
         * It replaces any text of the form "{configname}" (where configname is the name 
         * of a config parameter in configuration.php) with the value of the parameter.
         *
         * This is similar to shortcodes functionality within wordpress
         */

        // The line below restricts the functionality to the site (ie not on api)
        // You may not want this, so you need to consider this in your own plugins
        if (!$this->getApplication()->isClient('site')) {
            return;
        }
         
        // use this format to get the arguments for both Joomla 4 and Joomla 5
        // In Joomla 4 a generic Event is passed
        // In Joomla 5 a concrete ContentPrepareEvent is passed
        [$context, $article, $configParams, $page] = array_values($event->getArguments());
        if ($context !== "com_content.article" && $context !== "com_content.featured") return;
        
        // Get the app token and client identifier from the plugin parameters
        $appToken = $this->params->get('app_token', '');
        $clientIdentifier = $this->params->get('client_identifier', '');
        //$this->position = $this->params->get('position', '');

        $boolMap = ['false' => '0', 'true' => '1'];

        // Default parameters from the manifest
        $defaultParams = [
            'microdata' => $this->params->get('microdata', '1'),
            'responsive' => $this->params->get('responsive', '1'),
            'template' => $this->params->get('template', 'default.php'),
            'loadCSS' => $this->params->get('loadcss', '1'),
            'iframe_attrs' => $this->params->get('iframe_attrs', '')
        ];

        $srcParams = ['muted', 'autoplay', 'autopause', 'controls', 'loop']; 

        // Filter parameters with non-empty values
        $filteredSrcParams = array_filter(
            array_combine($srcParams, array_map(fn($k) => $this->params->get($k, ''), $srcParams)),
            fn($v) => $v !== ''
        );        
        
        // Match the shortcode
        if (preg_match('/{vimeo_video\s+id=(\d+)(.*?)}\s*/i', $article->text, $matches)) {
            $videoId = $matches[1];
            $attributes = trim($matches[2]);
            // Parse additional parameters
            preg_match_all('/(muted|autoplay|autopause|controls|loop|microdata|responsive|position|template)=(["\']?)(.*?)\2(?:\s+|$)/i', $attributes, $attrMatches, PREG_SET_ORDER);

            $configParams = $defaultParams; // Start with defaults
        
            foreach ($attrMatches as $attr) {
                $key = strtolower($attr[1]);
                $value = $attr[3];
        
                // Handle boolean parameters
                if (isset($boolMap[strtolower($value)])) {
                    $configParams[$key] = $boolMap[strtolower($value)];
                } elseif ($key === 'position' && in_array($value, ['', 'after_title', 'before_content', 'after_content'], true)) {
                    // Validate position
                    $configParams[$key] = $value;
                } elseif ($key === 'template' && preg_match('/^[\w-]+\.php$/i', $value)) {
                    // Validate template filename
                    $configParams[$key] = $value;
                } else {
                    if ($value == '') unset($configParams[$key]);
                }
            }
            $this->position = (isset($configParams['position']) ? $configParams['position'] : $this->params->get('position', ''));
        } else {
            // Handle invalid or malformed shortcodes
            Log::add('Invalid Vimeo shortcode detected', Log::WARNING, 'plg_content_vimeoapi');
            return;
        }
        
        // Fetch video info from Vimeo API
        $videoInfo = $this->getVideoInfo($videoId, $appToken);
        if ($videoInfo) {
            // Generate output based on the options
            $this->generateEmbedOutput($videoId, $clientIdentifier, $videoInfo, $configParams, $filteredSrcParams);
            if($this->position == '') {
                $article->text = str_replace($matches[0], $this->videoOutput, $article->text);
            } else {
                $article->text = str_replace($matches[0], '', $article->text);
            }
        }
    }

    public function displayAfterTitle(Event $event)
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }
        [$context, $article, $params, $page] = array_values($event->getArguments());
        if ($context !== "com_content.article" && $context !== "com_content.featured") return;
        if($this->position != 'after_title') return; 

        $eventType = method_exists($event, 'getContext') ? $this->videoOutput : "";
        
        if ($event instanceof ResultAwareInterface) {
            $event->addResult("{$eventType}");
        } else {
            $result = $event->getArgument('result') ?? [];
            $result[] = "{$eventType}";
            $event->setArgument('result', $result);
        }
    }

    public function displayBeforeContent(Event $event)
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }
        [$context, $article, $params, $page] = array_values($event->getArguments());
        if ($context !== "com_content.article" && $context !== "com_content.featured") return;
        if($this->position != 'before_content') return;

        $eventType = method_exists($event, 'getContext') ? $this->videoOutput : "";
        
        if ($event instanceof ResultAwareInterface) {
            $event->addResult("{$eventType}");
        } else {
            $result = $event->getArgument('result') ?? [];
            $result[] = "{$eventType}";
            $event->setArgument('result', $result);
        }
    }

    public function displayAfterContent(Event $event)
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }
        [$context, $article, $params, $page] = array_values($event->getArguments());
        if ($context !== "com_content.article" && $context !== "com_content.featured") return;
        if($this->position != 'after_content') return;

        $eventType = method_exists($event, 'getContext') ? $this->videoOutput : "";
        
        if ($event instanceof ResultAwareInterface) {
            $event->addResult("{$eventType}");
        } else {
            $result = $event->getArgument('result') ?? [];
            $result[] = "{$eventType}";
            $event->setArgument('result', $result);
        }
    }

    protected function getVideoInfo($videoId, $appToken)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.vimeo.com/videos/" . $videoId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the HTTP method to GET
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        // Set the headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $appToken",
            "Content-Type: application/json"
        ));
        
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            Log::add('API Request Error: ' . $error, LOG::ERROR, 'vimeoapi.api.error');
            Log::add('HTTP Response Code: ' . $httpCode, LOG::ERROR, 'vimeoapi.api.response');
            return; // Handle the error as needed
        }
        Log::add($response, LOG::DEBUG, 'vimeoapi.api.response');
        return json_decode($response, true);
    }

    protected function generateEmbedOutput($videoId, $clientIdentifier, $videoInfo, $configParams, $filteredSrcParams)
    {

        $templateFile = $configParams['template'];
        // Get the selected template from the plugin parameters
        $selectedTemplate = $this->params->get('template', '');

        // Define the path to the template file
        $templatePath = JPATH_PLUGINS . "/content/vimeoapi/tmpl/$templateFile"; // Adjusted path
    
        // Check if the template file exists
        if (file_exists($templatePath)) {
            // Start output buffering
            ob_start();
            
            // Include the template file
            include $templatePath;
    
            // Get the output from the buffer
            $this->videoOutput = ob_get_clean();
        } else {
            // Fallback output if the template does not exist
            $this->videoOutput = '<div class="error">Template not found.</div>';
        }
        //$this->videoOutput = $output;
    }
}