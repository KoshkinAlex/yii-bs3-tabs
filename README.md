Bootstrap 3 tabs widget for Yii 1.* framework
==========

## Requirements

1. Yii framework (minimum tested version 1.13)
2. PHP 5.4


## Installation

Copy all files to your widgets directory


## Usage
```
use \Widgets\Tabs\TabsWidget;

$this->widget(
    TabsWidget::class,
    [
	'loadType' => TabsWidget::LOAD_AJAX, // Can be also:  TabsWidget::LOAD_ONETIME || TabsWidget::LOAD_GET_PARAM
	'items' => [
		[
			'id' => 'main',
			'name'=>'Common info',
			'content'=> function () use ($model)  {
			    // Callback function is executed only if tab content is visible and should be rendered (see: $this->loadType)
				return $this->renderPartial('vewpath/viewname', ['model' => $model], true);
			},
		],
		[
			'id' => 'othertab',
			'name' => 'Other tab heading',
			'active' => true, // This tab is opened
			'visible' => Yii::app()->user->checkAccess('viewTabRule'),
			'content' => "Simple prerendered text",
		],
		[
			'id' => 'redirect',
			'name' => "Redirect tab",
			'redirectUrl' => "http://google.com",
		],
		[
			'id' => 'otherload',
			'name' => 'Tab with other load type',
			'loadType'=> TabsWidget::LOAD_ONETIME,
		],
	],
]);

```

## Tabs properties

**name** *(string)* Tab heading text label
**id** *(string)* Tab string identifier
**loadType** *(integer)* Each tab can be loaded with custom method, by default widgets $loadType property is used
**redirectUrl** *(string)* If is set tab redirects to this page, without dynamic content loading
**active** *(boolean)* Flag that shows currently selected tab
**visible** *(boolean)* Visibility flag. Invisible tabs are not processed.
**content** *(string)* or *(function)* that returns content html
**htmlOptions** *(array)* passed to htmlOptions attribute
