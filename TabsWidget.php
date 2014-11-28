<?php
/**
 * @author: Koshkin Alexey (koshkin.alexey@gmail.com)
 */
namespace Widgets\Tabs;

/**
 * Class TabsWidget
 * @package Widgets\Tabs
 *
 * Renders bootstrap-3 tabs panel. Content of tabs can be rendered simultaneously with widget, or later, using AJAX or
 * fetching $_GET[$this->getParam] from request string. If LOAD_AJAX render mode is used, widget cleans previous PHP
 * output, prints tab content and terminates script execution. This allows to use one controller action for all render
 * types, but you should keep in mind not no do hard work before calling widget this way (it will be lost).
 */
class TabsWidget extends \CWidget {

	const LOAD_ONETIME = 1;		// Tabs content is rendered simultaneously with widget
	const LOAD_GET_PARAM = 2;	// Tab content is rendered when $_GET[$this->getParam] param points to currently opened tab
	const LOAD_AJAX = 3;		// Tab content is loaded by AJAX when tab opens

	/** @var array Array of tabs. Each element can contain following keys:	 *
	 * 	'name'			(string) Tab heading text label
	 * 	'id'			(string) Tab string identifier
	 * 	'loadType'		(integer) Each tab can be loaded with custom method, by default widgets $loadType property is used
	 * 	'redirectUrl'	(string) If is set tab redirects to this page, without dynamic content loading
	 * 	'active'		(boolean) Flag that shows currently selected tab
	 * 	'visible'		(boolean) Visibility flag. Invisible tabs are not processed.
	 * 	'content'		(string) or (function) that returns content html
	 * 	'htmlOptions'	(array) passed to htmlOptions attribute
	 * */
	public $items = [];

	/** @var int All tabs loading type (can be customized for each tab separately) */
	public $loadType = self::LOAD_ONETIME;

	/** @var false|string Url that is used to load tabs content. By default current controller + action + GET params */
	public $loadUrl = false;

	/** @var string $_GET parameter name that points to opened tab */
	public $getParam = 'opentab';

	/** @var string This sting is added to all tab ids */
	public $tabIdPrefix = 'tab';

	/** @var bool|string Open tab id */
	public $openTab = false;

	/** @var array htmlOptions for tab panel container */
	public $tabPanelOptions = [];

	/** @var null|string Replace content of currently opened tab  */
	public $tabContent = null;

	/** @var false|integer Open tab index */
	private $_openTabNum = false;

	/**
	 * Initialize widget
	 * @throws \CException
	 */
	public function init()
	{
		if ($this->loadType == self::LOAD_GET_PARAM || $this->loadType == self::LOAD_AJAX) {
			if (!$this->loadUrl) {
				$this->loadUrl[0] = \Yii::app()->urlManager->parseUrl(\Yii::app()->request);
				foreach ($_GET as $k => $v) {
					if (in_array($k, ['r'])) continue;
					$this->loadUrl[$k] = $v;
				}
			}
		}

		$tabFromRequest = \Yii::app()->request->getParam($this->getParam, false);
		if ($this->openTab) $this->openTab = $this->tabIdPrefix . $this->openTab;
		if ($tabFromRequest) $this->openTab = $tabFromRequest;

		foreach ($this->items as $i => $item) {
			if (isset($item["visible"]) && !$item["visible"]) {
				unset($this->items[$i]);
			} else {
				$this->items[$i]['num'] = $i;
				$this->normalizeTabSettings($this->items[$i]);
			}
		}

		$url = \Yii::app()->getAssetManager()->publish(__DIR__ . '/assets');
		\Yii::app()->getClientScript()
			->registerScriptFile($url . '/tabs-widget.js')
			->registerScriptFile($url . '/bootstrap-tabdrop/js/bootstrap-tabdrop.js')
			->registerCssFile($url . '/bootstrap-tabdrop/css/tabdrop.css');
	}

	/**
	 * Run widget
	 */
	public function run()
	{

		$labels = [];
		$tabs = [];

		// Load single tab content for ajax request
		if (
			\Yii::app()->request->getIsAjaxRequest()
			&& $this->_openTabNum !== false
			&& !empty($this->items[$this->_openTabNum])
			&& $this->items[$this->_openTabNum]['loadType'] == self::LOAD_AJAX
		) {
			ob_end_clean();
			echo $this->items[$this->_openTabNum]['content'];
			\Yii::app()->end();
		}

		foreach ($this->items as $item) {
			$linkOptions = $item['htmlOptions'];
			$linkOptions['role'] = 'tab';
			if ($item['loadType'] == self::LOAD_GET_PARAM) {
				$linkOptions['data-toggle'] = false;
			} else {
				$linkOptions['data-toggle'] = 'tab';
			}

			$tabPanelOptions = $this->tabPanelOptions;
			if (empty ($tabPanelOptions['class'])) {
				$tabPanelOptions['class'] = '';
			}

			$tabPanelOptions['class'] .= ' tab-pane';
			if ($item['active']) {
				$tabPanelOptions['class'] .= ' active';
			}
			$tabPanelOptions['id'] = $item['id'];

			$labels[] = \CHtml::tag(
				'li',
				['class' => ($item['active'] ? 'active' : '')],
				\CHtml::link($item['name'], $item['url'], $linkOptions)
			);
			$tabs[] = \CHtml::tag('div', $tabPanelOptions, $item['content']);
		}

		echo \CHtml::tag('ul', ['class' => 'nav nav-tabs', 'role' => 'tablist'], join('', $labels));
		echo \CHtml::tag('div', ['class' => 'tab-content'], join('', $tabs));
	}

	/**
	 * Set and preprocess all tab properties
	 * @param array $tabSettings
	 */
	protected function normalizeTabSettings(&$tabSettings)
	{

		if (empty($tabSettings['id'])) {
			$tabSettings['id'] = $this->getId() . $tabSettings['num'];
		}

		$tabID = $tabSettings['id'] = strval($this->tabIdPrefix . $tabSettings['id']);

		if (empty($tabSettings['loadType']) || !is_int($tabSettings['loadType'])) {
			$tabSettings['loadType'] = $this->loadType;
		}

		if (!empty($tabSettings['redirectUrl'])) {
			$tabSettings['loadType'] = self::LOAD_GET_PARAM;
			$tabSettings['url'] = $tabSettings['redirectUrl'];
		} elseif ($tabSettings['loadType'] == self::LOAD_GET_PARAM || $tabSettings['loadType'] == self::LOAD_AJAX) {
			$dataLoadUrl = $this->loadUrl;
			$dataLoadUrl[$this->getParam] = $tabID;
			if (empty($tabSettings['url'])) $tabSettings['url'] = \CHtml::normalizeUrl($dataLoadUrl);
		}

		if (!isset($tabSettings['url']) || !is_string($tabSettings['url'])) $tabSettings['url'] = '';
		if ($tabSettings['loadType'] == self::LOAD_AJAX || $tabSettings['loadType'] == self::LOAD_ONETIME) {
			$tabSettings['url'] .= '#' . $tabID;
		}

		if (empty($tabSettings['name']) || !is_string($tabSettings['name'])) {
			$tabSettings['name'] = "Tab " . $tabSettings['num'];
		}

		// Setting active tab
		if ($this->openTab === false || $tabID === $this->openTab || (!empty($tabSettings['active']) && $this->openTab === false)) {
			$this->openTab = $tabID;
			$this->_openTabNum = $tabSettings['num'];
			$tabSettings['active'] = true;
		} else {
			$tabSettings['active'] = false;
		}

		if (empty ($tabSettings['htmlOptions']) || !is_array($tabSettings['htmlOptions'])) {
			$tabSettings['htmlOptions'] = [];
		}

		// Render tab content
		if ($this->loadType == self::LOAD_ONETIME || $tabSettings['active']) {
			if (!empty ($tabSettings['content']) && is_callable($tabSettings['content'])) {
				$tabSettings['content'] = $tabSettings['content']();
			}
		}

		if (empty ($tabSettings['content']) || !is_string($tabSettings['content'])) {
			if ($this->tabContent && $tabSettings['active']) $tabSettings['content'] = $this->tabContent;
			else $tabSettings['content'] = '';
		}

	}

}