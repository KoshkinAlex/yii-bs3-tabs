Yii 1.* Bootstrap 3 tabs widget 
==========
Renders bootstrap-3 tabs panel. Content of tabs can be rendered simultaneously with widget, or later, using AJAX or 
fetching $_GET[$this->getParam] from request string.
 
## Requirements

1. Yii framework (minimum tested version 1.13)
2. PHP 5.4


## Usage
```php
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

## Loading types

**LOAD_ONETIME = 1**
Tabs content is rendered simultaneously with widget.

**LOAD_GET_PARAM = 2**
Tab content is rendered when $_GET[$this->getParam] param points to currently opened tab.

**LOAD_AJAX = 3**
Tab content is loaded by AJAX when tab opens. Active tab renders with widget and does not require other HTTP request. 
If this render mode is used, widget cleans previous PHP output, prints tab content and terminates script execution. 
This allows to use one controller action for all render types, but you should keep in mind not no do hard work before 
calling widget this way (it will be lost).

## Tabs properties

**name** *(string)* Tab heading text label
**id** *(string)* Tab string identifier
**loadType** *(integer)* Each tab can be loaded with custom method, by default widgets $loadType property is used
**redirectUrl** *(string)* If is set tab redirects to this page, without dynamic content loading
**active** *(boolean)* Flag that shows currently selected tab
**visible** *(boolean)* Visibility flag. Invisible tabs are not processed.
**content** *(string)* or *(function)* that returns content html
**htmlOptions** *(array)* passed to htmlOptions attribute
