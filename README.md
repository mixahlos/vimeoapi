# VimeoAPI Plugin for Joomla



## Description

The **VimeoAPI Plugin** is a content plugin for Joomla 4 & Joomla 5 that allows users to easily embed Vimeo videos into articles using shortcodes. The plugin provides advanced customization options such as autoplay, muted videos, and template selection to enhance video presentation in your Joomla-powered website.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Parameters](#parameters)
- [CSS Styling](#css-styling)
- [Contributing](#contributing)
- [License](#license)

## Features

- Embed Vimeo videos with simple shortcodes.
- Customizable options for:
  - Autoplay
  - Muted mode
  - Autopause
  - Video controls
  - Microdata inclusion
  - Responsiveness
  - Custom templates
  - Display position in articles
  - Optional CSS loading
  - Custom HTML attributes for the iframe element.
  - API token end client identifier for authenticated Vimeo access.

## Installation

1. Download the plugin ZIP file from the [releases](https://github.com/your-repo/vimeoapi/releases)[.](https://github.com/your-repo/vimeoapi/releases)
2. Log in to your Joomla administrator panel.
3. Navigate to **Extensions > Install**.
4. Upload the plugin ZIP file and click **Install**.
5. Go to **Extensions > Plugins**, search for `VimeoAPI`, and enable the plugin.
6. In the plugin settings, enter your **API Token** and **Client Identifier** to enable Vimeo API integration.

## Usage

### Additional Templates

The plugin includes an experimental template file that uses the `<video>` HTML element with multiple video sources instead of an iframe. This template is not fully tested due to a lack of a Pro account and may require adjustments to fit specific use cases.

To embed a Vimeo video in an article, use the following shortcode format:

```html
{vimeo_video id=VIDEO_ID autoplay=true muted=false}
```

### Examples:

- Basic video:
  ```html
  {vimeo_video id=123456789}
  ```
- Video with autoplay and no controls:
  ```html
  {vimeo_video id=123456789 autoplay=true controls=false}
  ```
- Responsive video with custom template:
  ```html
  {vimeo_video id=123456789 responsive=true template=custom.php}
  ```

## Parameters

The plugin supports the following parameters:

### Shortcode Parameters

The following parameters can be used in the shortcode to customize video embedding:

| Parameter    | Type    | Values                                                 | Default          | Description                                |
| ------------ | ------- | ------------------------------------------------------ | ---------------- | ------------------------------------------ |
| `id`         | Integer | Vimeo video ID                                         | Required         | The unique ID of the Vimeo video.          |
| `autoplay`   | Boolean | `true`, `false`, or unset (`""`)                       | `false`          | Enable or disable autoplay.                |
| `muted`      | Boolean | `true`, `false`, or unset (`""`)                       | `false`          | Start video muted or unmuted.              |
| `autopause`  | Boolean | `true`, `false`, or unset (`""`)                       | `true`           | Pause video when another starts playing.   |
| `controls`   | Boolean | `true`, `false`, or unset (`""`)                       | `true`           | Show or hide video controls.               |
| `microdata`  | Boolean | `true`, `false`, or unset (`""`)                       | `true`           | Include schema.org metadata for SEO.       |
| `responsive` | Boolean | `true`, `false`, or unset (`""`)                       | `true`           | Enable responsive video embedding.         |
| `position`   | String  | `""`, `after_title`, `before_content`, `after_content` | `""`  (In Place) | Where to display the video in the article. |



### Plugin Configuration Parameters

The following parameters can only be configured in the plugin settings. Additionally, all shortcode parameters except `id` can also be configured here:

| Parameter           | Type    | Values                          | Default  | Description                              |
| ------------------- | ------- | ------------------------------- | -------- | ---------------------------------------- |
| `app_token`         | String  | Any valid Vimeo API token       | Required | Token for Vimeo API authentication.      |
| `client_identifier` | String  | Client identifier for Vimeo API | Required | Identifier for Vimeo API authentication. |
| `loadcss`           | Boolean | `true`, `false`                 | `true`   | Whether to load the plugin's CSS file.   |
| `iframe_attrs`      | String  | Any valid HTML attributes       | `""`     | Custom attributes to be added t ame.     |

## CSS&#x20;

The plugin includes an optional CSS file for styling the `responsive-video` class. You can customize the video appearance by editing the CSS file located at:

```
/plugins/content/vimeoapi/css/vimeoapi.css
```

If you prefer not to load the CSS, you can disable it in the plugin settings by setting the **Load CSS** option to `No`.

To ensure the CSS file is loaded by the plugin, use the following method in your template:

```php
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('vimeoapi', 'plugins/content/vimeoapi/css/vimeoapi.css');
```

## Contributing

Contributions are welcome! To contribute:

1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Submit a pull request with detailed information about your changes.

## License

This project is licensed under the GPL License. See the [LICENSE](LICENSE) file for details.
