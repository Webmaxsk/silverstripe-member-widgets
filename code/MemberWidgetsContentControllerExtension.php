<?php
/**
 * Add this to ContentController to enable widgets
 *
 * @package widgets
 */
class MemberWidgetsContentControllerExtension extends WidgetContentControllerExtension
{
    /**
     *
     * @var array
     */
    private static $allowed_actions = array(
        'handleWidget'
    );

    /**
     * Handles widgets attached to a page through one or more {@link WidgetArea}
     * elements.
     *
     * Iterated through each $has_one relation with a {@link WidgetArea} and
     * looks for connected widgets by their database identifier.
     *
     * Assumes URLs in the following format: <URLSegment>/widget/<Widget-ID>.
     *
     * @return RequestHandler
     */
    public function handleWidget()
    {
        $SQL_id = $this->owner->getRequest()->param('ID');
        if (!$SQL_id) {
            return false;
        }

        // find WidgetArea relations
        $widgetAreaRelations = array();
        $hasOnes = $this->owner->data()->hasOne();

        if (!$hasOnes) {
            return false;
        }

        foreach ($hasOnes as $hasOneName => $hasOneClass) {
            if ($hasOneClass == 'WidgetArea' || is_subclass_of($hasOneClass, 'WidgetArea')) {
                $widgetAreaRelations[] = $hasOneName;
            }
        }

        // find widget
        $widget = null;

        foreach ($widgetAreaRelations as $widgetAreaRelation) {
            if ($widget) {
                break;
            }

            $widget = $this->owner->data()->$widgetAreaRelation()->Widgets()
                ->filter('ID', $SQL_id)
                ->First();
        }

        if (!$widget && (!($widget = DataObject::get_by_id('Widget',$SQL_id)) || !$widget->Parent()->is_a('MemberWidgetArea'))) {
            user_error('No widget found', E_USER_ERROR);
        }

        return $widget->getController();
    }
}
