<?php

/**
 * Bootstrap modal with AJAX loading content.
 * The trigger link\button should have data-toggle='modal', data-target='#{modalId}', data-source='{actionUrl}' attributes,
 * @author chervand <chervand@gmail.com>
 * todo id from htmlOptions
 * todo: register whole js
 */
class YDModalWidget extends CWidget
{
	public $htmlOptions = array();
	public $reloadOnClose = false;
	public $actions;

	public function init()
	{
		if (!isset($this->htmlOptions['id']) || empty($this->htmlOptions['id']))
			$this->htmlOptions['id'] = $this->id;
		if (!isset($this->htmlOptions['title']) || empty($this->htmlOptions['title']))
			$this->htmlOptions['title'] = 'Modal';
		if (!isset($this->htmlOptions['width']) || empty($this->htmlOptions['width']))
			$this->htmlOptions['width'] = '600px';
		if (!isset($this->htmlOptions['data-backdrop']) || empty($this->htmlOptions['data-backdrop']))
			$this->htmlOptions['data-backdrop'] = 'static';

		$js = '$(document).on("click", "[data-toggle=modal]",function(){';
		$js .= '$(document).qP().modal().load($(this).data("target"),$(this).data("source"));';
		if ($this->reloadOnClose === true)
			$js .= '$($(this).data("target")).on("hidden.bs.modal",function(){location.reload();});';
		$js .= '});';

		Yii::app()->clientScript->registerScript('qModal', $js, CClientScript::POS_READY);
	}

	public function run()
	{
		echo CHtml::openTag('div', array(
			'id' => $this->htmlOptions['id'],
			'class' => 'modal fade',
			'data-backdrop' => $this->htmlOptions['data-backdrop'],
			'tabindex' => '-1',
		));
		echo CHtml::openTag('div', array(
			'style' => 'width: ' . $this->htmlOptions['width'],
			'class' => 'modal-dialog'
		));
		echo CHtml::tag('div', array(
			'class' => 'modal-content',
		), '&nbsp');
		echo CHtml::closeTag('div');
		echo CHtml::closeTag('div');
	}

	/**
	 * @param $text
	 * @param string $url
	 * @param array $htmlOptions
	 * @return string
	 */
	public function button($text, $url = '#', $htmlOptions = [])
	{
		return CHtml::htmlButton($text, array_merge([
			'data-toggle' => 'modal',
			'data-target' => '#' . $this->id,
			'data-source' => $url
		], $htmlOptions));
	}

	/**
	 * GridView usage example:
	 *    [
	 *        'type' => 'raw',
	 *        'value' => function ($model) use ($modal) {
	 *                return $modal->link(
	 *                    '<i class="fa fa-edit"></i>',
	 *                    $modal->createActionUrl('delete', $model, 'name'),
	 *                    ['title' => Yii::t('app', 'Edit')]
	 *                );
	 *            }
	 *    ]
	 * @param $text
	 * @param string $url
	 * @param array $htmlOptions
	 * @return string
	 */
	public function link($text, $url = '#', $htmlOptions = [])
	{
		return CHtml::link($text, '#', array_merge([
			'data-toggle' => 'modal',
			'data-target' => '#' . $this->id,
			'data-source' => $url
		], $htmlOptions));
	}

	public function createActionUrl($action, $model = null, $keys = null, array $params = [])
	{
		$url = $action;

		if (
			isset($this->actions) &&
			is_array($this->actions) &&
			array_key_exists($action, $this->actions)
		) {
			$url = $this->actions[$action];
		}

		if (
			isset($model) && $model instanceof CActiveRecord &&
			isset($keys)
		) {
			if (!is_array($keys))
				$keys = [$keys];

			foreach ($keys as $key) {
				$params[get_class($model) . '[' . $key . ']'] = $model->$key;
			}
		}

		return Yii::app()->createUrl($url, $params);
	}
}
