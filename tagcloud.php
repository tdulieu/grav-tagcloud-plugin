<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;

class TagCloudPlugin extends Plugin
{
    /**
     * @var boolean
     */
    protected $active = false;

    /**
     * @var array
     */
    protected $tag_cloud;

    /**
     * @var string
     */
    protected $cache_id;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onBuildPagesInitialized' => ['onBuildPagesInitialized']
        ];
    }

    /**
     * Declare event hooks.
     */
    public function onPluginsInitialized()
    {
        $this->cache_id = hash('md5', 'plugin_tagcould_data' . $this->grav['cache']->getKey());

        if ($this->isAdmin()) {
            $this->enable([
                'onFormProcessed' => ['onFormProcessed', 0]
            ]);
        } else {
            $this->enable([
                'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
                'onPageInitialized' => ['onPageInitialized', 0],
            ]);
        }
    }

    /**
     * Clear the plugin cache when pages are rebuild.
     */
    public function onBuildPagesInitialized()
    {
        $this->grav['debugger']->addMessage('TagCloud::onBuildPagesInitialized()');
        $this->clearCache();
    }

    /**
     * Clear the cache when the plugin form is submitted.
     */
    public function onFormProcessed()
    {
        $this->grav['debugger']->addMessage('TagCloud::onFormProcessed()');
        $this->clearCache();
    }

    /**
     * Make plugin template path available to twig.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Inject twig variables when a page is initialized.
     */
    public function onPageInitialized()
    {
        $this->enable([
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ]);
    }

    /**
     * Add tag_cloud variable to twig, optionally add builtin css.
     */
    public function onTwigSiteVariables()
    {
        $this->grav['twig']->twig_vars['tag_cloud'] = $this->getTagCloud();

        if ($this->config->get('plugins.tagcloud.built_in_css')) {
            $this->grav['assets']->add('plugin://tagcloud/css/tagcloud.css');
        }
    }

    /**
     * Clear the plugin cache.
     */
    protected function clearCache()
    {
        $this->grav['cache']->delete($this->cache_id);
    }

    /**
     * Return the tag cloud array.
     *
     * @return array
     */
    protected function getTagCloud()
    {
        if (!$this->tag_cloud) {
            $this->tag_cloud = $this->buildTagCloud();
        }
        return $this->tag_cloud;
    }

    /**
     * Load the tag cloud data from cache, or build it from taxonomy.
     *
     * @return array
     */
    protected function buildTagCloud()
    {
        if ($tag_cloud = $this->grav['cache']->fetch($this->cache_id)) {
            return $tag_cloud;
        }

        $tag_cloud = [];
        $tags = [];
        $minCount = PHP_INT_MAX;
        $maxCount = 0;
        $taxonomy = $this->grav['taxonomy']->taxonomy();
        $term = $this->config->get('plugins.tagcloud.taxonomy');

        foreach ($taxonomy[$term] as $tag => $items) {
            $count = count($items);

            if ($count < $minCount) {
                $minCount = $count;
            }
            if ($count > $maxCount) {
                $maxCount = $count;
            }
            $tags[strval($tag)] = $count;
        }

        if (!empty($tags)) {
            $minSize = floatval($this->config->get('plugins.tagcloud.min_size'));
            $maxSize = floatval($this->config->get('plugins.tagcloud.max_size'));

            if ($minCount === $maxCount) {
                $ratio = 0;
            } else {
                $ratio = ($maxSize - $minSize) / ($maxCount - $minCount);
            }

            foreach ($tags as $tag => $count) {
                $tag_cloud[strval($tag)] = array(
                    'count' => $count,
                    'size' => $minSize + $ratio * ($count - $minCount)
                );
            }

            $order_by = $this->config->get('plugins.tagcloud.order_by');
            $order_dir = $this->config->get('plugins.tagcloud.order_dir');

            if ($order_by == 'name') {
                if ($order_dir == 'asc') {
                    ksort($tag_cloud);
                } else {
                    krsort($tag_cloud);
                }
            } else {
                if ($order_dir == 'asc') {
                    asort($tag_cloud, function($a, $b) {
                        return $a['count'] - $b['count'];
                    });
                } else {
                    arsort($tag_cloud, function($a, $b) {
                        return $a['count'] - $b['count'];
                    });
                }
            }

            $count = intval($this->config->get('plugins.tagcloud.count'));
            if ($count > 0) {
                $tag_cloud = array_slice($tag_cloud, 0, $count);
            }
        }

        $this->grav['cache']->save($this->cache_id, $tag_cloud);
        return $tag_cloud;
    }
}
