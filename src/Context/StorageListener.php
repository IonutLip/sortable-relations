<?php

namespace Bolt\Extension\Printi\SortableRelations\Context;

use Bolt\Events\StorageEvent;
use Bolt\Events\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class StorageListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            StorageEvents::POST_SAVE => 'saveRelationOrder',
        );
    }

    public function saveRelationOrder(StorageEvent $event)
    {
        $content = $event->getContent();
        $contenttype = $event->getContentType();
        $relations = $content->relation;
        foreach ($relations as $type => $related) {
            // First we delete the current values
            $tablename = $this->app['storage']->getTablename('relations');
            $this->app['db']->delete($tablename, [
                'from_contenttype' => $contenttype,
                'to_contenttype' => $type,
                'from_id' => $content->id
            ]);

            // Now we insert all the ones we have, along with the order
            foreach ($related as $sortOrder => $relId) {
                $row = [
                    'from_contenttype' => $contenttype,
                    'from_id'          => $content->id,
                    'to_contenttype'   => $type,
                    'to_id'            => $relId,
                    'sort'             => $sortOrder
                ];
                $this->app['db']->insert($tablename, $row);
            }

        }
    }

}
