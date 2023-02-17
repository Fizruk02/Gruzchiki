<?php

namespace App\Http\Livewire;

use Illuminate\Database\Eloquent\Model;
use LaravelViews\Actions\Action;
use LaravelViews\Views\Traits\WithActions;
use LaravelViews\Views\View;
use Exception;

trait WithExport
{
    public function rowExport(Model $model)
    {
        return $this->row($model);
    }
    public function headersExport(): array
    {
        return $this->headers();
    }

    public function getExportData() {
        $models = $this->query->get();

        $headers = [];
        foreach ($this->headersExport() as $header) {
            $headers[] = $header;
        }
        $items = [];
        foreach ($models as $model) {
            $items[] = $this->rowExport($model);
        }
        return [
            'headers' => $headers,
            'items' => $items,
        ];
    }

    /** For export actions */
    protected function exportActions()
    {
        return [
        ];
    }

    public function getExportActionsProperty()
    {
        if (method_exists($this, 'exportActions')) {
            return $this->exportActions();
        }

        return [];
    }

    public function getHasExportActionsProperty()
    {
        return method_exists($this, 'exportActions') && count($this->exportActions());
    }

    public function executeExportAction($action)
    {
        $this->executeExportHandler($action);
    }

    private function executeExportHandler($actionId, $actionableItemId = null)
    {
        /** @var Action  */
        $action = $this->findExportAction($actionId);

        if ($action) {
            $actionableItems = $actionableItemId ? $this->getModelWhoFiredAction($actionableItemId) : $this->selected;

            $action->view = $this;
            $action->handle($actionableItems, $this);
        } else {
            throw new Exception("Unable to find the {$actionId} action");
        }
    }

    /**
     * Finds an action by its id
     */
    private function findExportAction(string $actionId)
    {
        $actions = collect($this->exportActions);
        return $actions->first(
            function ($actionToFind) use ($actionId) {
                return $actionToFind->id === $actionId;
            }
        );
    }
}
