# Grav Tag Cloud Plugin

The **tagcloud** plugin displays a tag cloud on your [Grav][grav] site. It is mostly useful with a tag-based taxonomy for posts on a blog.

![Tag Cloud](/assets/readme.png)

A live example of the **tagcloud** plugin is available on my Grav blog [Antyos][antyos] (click on the tags icon in the sidebar).

I started to use Grav only a few weeks ago. Help and advises from the community members would be greatly appreciated to improve this plugin.

## Installation

This plugin is not an official Grav plugin. Thus, you need to install it manually by following these easy steps:

* Download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`
* Rename the unzipped folder to `tagcloud`
* You should now have all the plugin files under `/your/site/grav/user/plugins/tagcloud`

## Configuration

The default configuration is provided in the `user/plugins/tagcloud/tagcloud.yaml` file:

``` yaml
enabled: true       # Set to false to completely disable this plugin
taxonomy: tag       # Taxonomy term used for building the tag cloud
min_size: 12        # Min tag font size
max_size: 36        # Max tag font size
unit: px            # Font size unit (px, em, etc.)
count: 0            # Number of tags to display (0 to show all tags)
show_count: false   # Display the number of occurences near each tag
order_by: name      # Sort criteria (name or count)
order_dir: asc      # Sort order (asc or desc)
built_in_css: true  # Use the provided stylesheet
```

To make your own modifications to the configuration options, you should copy the `user/plugins/tagcloud/tagcloud.yaml` file to the `user/config/plugins` folder. Then you can manually edit this file.

Alternatively, you can visit the plugin configuration page if you have installed the [admin][admin] plugin.

## Usage

To display the tag cloud on your site, use the provided template or copy the `user/plugins/tagcloud/templates/partials/tagcloud.html.twig` file to your own theme and customize it to suit your needs.

Then you can include the template in your theme (for example in the `sidebar.html.twig` partial):

``` twig
{% if config.plugins.tagcloud.enabled %}
  {% include 'partials/tagcloud.html.twig' %}
{% endif %}
```

## Styling

You can use the plugin default stylesheet or copy the contents of the `user/plugins/tagcloud/css/tagcloud.css` file to your own stylesheet and customize it.

[grav]: http://github.com/getgrav/grav
[admin]: https://github.com/getgrav/grav-plugin-admin
[antyos]: http://www.antyos.fr
