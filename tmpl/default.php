<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  vimeoapi
 *
 * @copyright   (C) 2024 Mike Economou
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * This is the default template file
 */

 defined('_JEXEC') or die;

 use Joomla\CMS\Factory;
 use Joomla\CMS\Uri\Uri;

// Load the CSS file
if($configParams['loadCSS'] === '1') {
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $pluginUrl = Uri::root() . 'plugins/content/vimeoapi/css/vimeoapi.css';
    $wa->registerAndUseStyle('vimeoapi', $pluginUrl);
}

// Construct the iframe source URL with parameters
$src = "https://player.vimeo.com/video/$videoId?badge=0";
foreach($filteredSrcParams as $k => $v) {
    $src .= "&$k=$v";
}
$src .= "&player_id=0";
$src .= "&app_id=$clientIdentifier"; // Replace with your actual app ID

// Start building the output

// Add microdata attributes if required
if ($includeMicrodata) {?>
    <div class="responsive-video vimeo-plugin" itemscope itemtype="http://schema.org/VideoObject">
    <meta itemprop="name" content="<?php echo htmlspecialchars($videoInfo['name'])?>">
    <meta itemprop="description" content="<?php echo htmlspecialchars($videoInfo['description'])?>">
    <meta itemprop="contentUrl" content="<?php echo htmlspecialchars($videoInfo['link'])?>" />
    <meta itemprop="embedUrl" content="<?php echo htmlspecialchars($videoInfo['player_embed_url'])?>" />
    <?php
    if (isset($videoInfo['pictures']['sizes'][0]['link'])) { ?>
        <meta itemprop="thumbnailUrl" content="<?php echo htmlspecialchars($videoInfo['pictures']['sizes'][0]['link'])?>">
    <?php } ?>
    <?php if (isset($videoInfo['release_time'])) { ?>
        <meta itemprop="uploadDate" content="<?php echo htmlspecialchars($videoInfo['release_time'])?>">
    <?php } 
    if (isset($videoInfo['duration'])) { 
        $minutes = floor($videoInfo['duration'] / 60);
        $seconds = $videoInfo['duration'] % 60;?>
        <meta itemprop="duration" content="PT<?php echo $minutes?>M<?php echo $seconds?>S" />
    <?php }        
} else {?>
    <div class="<?php echo ($configParams['responsive'] === '1') ? 'responsive-video ' : ''?>vimeo-plugin">
<?php }

// Embed iframe with additional attributes
?>
<iframe src="<?php echo $src ?>" 
width="560" height="315" frameborder="0" 
allow="autoplay; fullscreen; picture-in-picture; clipboard-write"<?php echo $configParams['iframe_attrs']?>> 
</iframe>
</div>