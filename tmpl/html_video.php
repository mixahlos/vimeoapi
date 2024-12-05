<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  vimeoapi
 *
 * @copyright   (C) 2024 Mike Economou
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 
 *  
 * This is the template file that generates HTML Video
 * It can generate multiple sources wuth srcset for various screen sizes
 *
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
    <div class="<?php echo ($configParams['responsive'] === '1') ? 'responsive-video ' : ''?>vimeo-plugin html-video">
<?php }

if (isset($videoInfo['files'])) {
    $bestQualityFile = $videoInfo['files'][0]; // You might want to sort by quality or pick a specific one
    $videoUrl = $bestQualityFile['link'];?>

    <video controls width="640" height="360">';
    <?php
    foreach ($videoInfo['files'] as $file) {
        $quality = $file['quality'];
        $videoUrl = $file['link'];

        // Define media queries based on quality
        $mediaQuery = "";
        if ($quality === "1080p") {
            $mediaQuery = "(min-width: 1024px)";
        } elseif ($quality === "720p") {
            $mediaQuery = "(max-width: 1024px)";
        } elseif ($quality === "480p") {
            $mediaQuery = "(max-width: 480px)";
        }
    ?>
    <source src="' . <?php echo htmlspecialchars($videoUrl)?> . '" type="video/mp4">
    <?php } ?>
    Your browser does not support the video tag.
    </video>
<?php } ?>
</div>