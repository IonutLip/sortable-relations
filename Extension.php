<?php

namespace Bolt\Extension\Printi\SortableRelations;

use Bolt\Application;
use Bolt\BaseExtension;
use Bolt\Extension\Printi\SortableRelations\Context\StorageListener;
use Symfony\Component\HttpFoundation\Request;

class Extension extends BaseExtension
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
        if ($this->app['config']->getWhichEnd() == 'backend') {
            $this->app['twig.loader.filesystem']->prependPath(__DIR__ . '/twig');
        }

        $this->app['htmlsnippets'] = true;
     //   $this->app['dispatcher']->addSubscriber(new StorageListener());

        $this->addTwigFunction('relationSort', 'relationSort');
        $this->addTwigFunction('getSortedRelations', 'getSortedRelations');
        $this->addTwigFunction('getSortedRelated', 'getSortedRelated');
    }

    public function initialize()
    {

         $this->app->before([$this, 'before']);
//        $this->app['integritychecker'] = $this->app->share(
//            function ($app) {
//                return new IntegrityChecker($app);
//            }
//        );
        // Add CSS file
      //  $this->addCSS('assets/select2.sortable.css');

        // Add javascript file
//        $this->addJavascript(
//            $this->app['resources']->getPath('extensions/vendor/assets/select2.sortable.min.js'),
//            array('late' => true, 'priority' => 1000)
//        );
       // $check = $this->app['integritychecker']->checkTablesIntegrity();

       //var_dump($this->app['resources']->getPath('extensions/local/printi/assets/select2.sortable.css')); die;
        if ($this->app['config']->getWhichEnd() == 'backend') {
          //  $this->addCss('assets/select2.sortable.css', 1);
         //   $this->addJavascript('assets/select2.sortable.min.js', 1);
        }

       // $this->app['dispatcher']->addListener(\Bolt\Events\StorageEvents::POST_SAVE, array($this, 'saveRelationOrder'));



    }

    /**
     * Before middleware function
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
        // execute only when in backend
        if ($this->app['config']->getWhichEnd() !== 'backend') {
            return;
        }

        // add CSS and Javascript files to all requests in the backend
        $this->addCSS('assets/select2.sortable.css');

    }

    public function relationSort($arr1, $arr2)
    {

        $index = [];
        foreach ($arr2 as $key => $obj) {
            $index[] = $key;
        }
        $compiled = [];
        foreach ($arr1 as $val) {
            $relatedId = $val['to_id'];
            $compiled[$relatedId] = $arr2[$relatedId];
            unset($index[array_search($relatedId, $index)]);
        }

        foreach ($index as $val) {
            $compiled[$val] = $arr2[$val];
        }

        return $compiled;
    }

    public function getSortedRelations($content, $relcontenttype)
    {

        if (isset($content['id'])) {
            $id = $content['id'];

            $fromcontenttype = $content->contenttype['slug'];

            $query = "SELECT * from bolt_relations WHERE from_id=$id AND from_contenttype='$fromcontenttype' AND to_contenttype='$relcontenttype' ORDER BY sort;";
            $result = $this->app['db']->fetchAll($query);

            return $result;
        } else {
            return [];
        }
    }

    public function getSortedRelated($content, $relcontenttype)
    {

        if(isset($content['id'])){
            $id = $content['id'];

            $fromcontenttype = $content->contenttype['slug'];

            $query = "SELECT * from bolt_relations WHERE from_id=$id AND from_contenttype='$fromcontenttype' AND to_contenttype='$relcontenttype' ORDER BY sort;";
            $result = $this->app['db']->fetchAll($query);

            $arr2 = $content->related();
            $arr1 = $result;

            $index = [];
            foreach ($arr2 as $key => $obj) {
                $index[] = $obj->id;
            }
            $compiled = [];
            foreach ($arr1 as $val) {
                $relatedId = $val['to_id'];
                $compiled[] = $arr2[array_search($relatedId, $index)];
            }

            return $compiled;
        } else {
            return [];
        }
    }




    public function getName()
    {
        return 'sortable-relations';
    }
}

