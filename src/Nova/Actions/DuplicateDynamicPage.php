<?php

namespace OptimistDigital\NovaPageManager\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use OptimistDigital\NovaPageManager\NovaPageManager;

class DuplicateDynamicPage extends Action
{
    use InteractsWithQueue, Queueable;


    public $showOnDetail = true;
    public $showOnIndex = false;
    public $showOnTableRow = true;
    public $name = 'Duplicate';
    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return Action::danger("Cannot duplicate multiple models simultaneously.");
        }

        $model = $models->first();
        $newModel = $model->replicate();

        // Override values from fields
        foreach ($fields->getAttributes() as $key => $value) {
            if(isset($value)){
                $newModel->$key = $value;
            }
        }

        if ($fields->locale != $model->locale) {
            $newModel->locale_parent_id = $model->id;
        }
        $newModel->locale = $fields->locale;

        $newModel->push();


        $newModel->save();

        return Action::message($this->getSuccessMessage($model, $newModel, $fields));
    }

    protected function getSuccessMessage(Model $originalModel, Model $newModel, ActionFields $fields) : String {
        return "Resource has been duplicated.";
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make("locale")->options(
                NovaPageManager::getLocales()
            )->displayUsingLabels()
        ];
    }
}
