<?php

namespace App\EventListener;

use App\Entity\Grade;
use App\Service\StatisticService;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;

class GradeListener
{
    public function __construct(
        private StatisticService $statisticService,
    ) {
    }

    /**
     * Called when a grade is created.
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Grade) {
            return;
        }

        // Log grade creation (could be extended to cache invalidation, notifications, etc.)
        $this->onGradeChange($entity);
    }

    /**
     * Called when a grade is updated.
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Grade) {
            return;
        }

        // Recalculate statistics after grade update
        $this->onGradeChange($entity);
    }

    /**
     * Called when a grade is deleted.
     */
    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Grade) {
            return;
        }

        // Recalculate statistics after grade deletion
        $this->onGradeChange($entity);
    }

    /**
     * Handle grade changes - invalidate caches and log changes.
     */
    private function onGradeChange(Grade $grade): void
    {
        // This is where you can add:
        // - Cache invalidation for statistics
        // - Event dispatching for grade changes
        // - Notifications to teachers/students
        // - Audit logging

        // For now, this serves as a hook point for future enhancements
        // The statistics will be calculated on-demand by StatisticService
    }
}
