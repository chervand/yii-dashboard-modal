<?php

/**
 * Class ModalEditAction
 * @author chervand <chervand@gmail.com>
 * @property array $models
 * todo allowed post actions!!!
 * todo create new
 * todo ajax submit
 * todo: do not insert on save, only on add
 */
class YDModalActionEdit extends YDModalAction
{
	/**
	 * Model names ({view var name} => {model name}).
	 * If array view variable name is not set, it will be set to "model{model name}"
	 * @var array
	 */
	private $_models = [];
	/**
	 * Modal partial view name.
	 * @var string
	 */
	public $modalView = '_modalEdit';
	/**
	 * Form DOM element ID.
	 * @var string
	 */
	public $formId = 'form-edit';

	/**
	 * @var callable
	 */
	public $afterSubmit;

	public function __construct($controller, $id)
	{
		parent::__construct($controller, $id);
	}

	/**
	 * Edit modal default action.
	 */
	public function run()
	{
		if (Yii::app()->request->isAjaxRequest) {
			$this->formValidate();
			$this->formRender();
		} else
			$this->formSubmit();
	}

	public function setModels($models)
	{
		foreach ($models as $varName => $params) {
			if (!is_array($params)) // @todo check if class exists
				$params = ['class' => $params];
			if (is_numeric($varName))
				$varName = 'model' . $params['class'];
			if (!isset($params['pk']))
				$params['pk'] = 'id';
			$this->_models[$varName] = $params;
		}
	}

	public function getModels()
	{
		return $this->_models;
	}

	/**
	 * Performs form AJAX validation.
	 * @todo move delete check to the form ?
	 */
	protected function formValidate()
	{
		if (isset($_POST['ajax']) && $_POST['ajax'] == $this->formId) {
			if (!isset($_POST['delete'])) {
				echo CActiveForm::validate(array_map(function ($params) {
					if (!$model = $this->loadModel($params['class'], 'POST', $params['pk']))
						$model = $this->loadModel($params['class'], null, $params['pk']);
					if (array_key_exists('scenario', $params))
						$model->setScenario($params['scenario']);
					return $model;
				}, $this->models));
			} else echo '{}';
			Yii::app()->end();
		}
	}

	/**
	 * Renders the form view.
	 */
	protected function formRender()
	{
		$models = [];
		foreach ($this->models as $varName => $params) {
			if (!$models[$varName] = $this->loadModel($params['class'], 'GET', $params['pk']))
				$models[$varName] = $this->loadModel($params['class'], null, $params['pk']);
			if (array_key_exists('scenario', $params))
				$models[$varName]->setScenario($params['scenario']);
		}
		$this->controller->renderPartial($this->modalView, $models, false, true);
		Yii::app()->end();
	}

	/**
	 * Submits the form.
	 */
	protected function formSubmit()
	{
		$transaction = Yii::app()->db->beginTransaction();
		try {
			if (isset($_POST['create']) || isset($_POST['save']))
				$this->submitSave();
			elseif (isset($_POST['delete']))
				$this->submitDelete();
			$transaction->commit();
		} catch (Exception $exception) {
			Yii::app()->user->setFlash(rand(), CJSON::encode([
				'title' => Yii::t('qadmin', 'Error'),
				'type' => 'error',
				'text' => $exception->getMessage(),
			]));
			$transaction->rollback();
		}
		$this->onAfterSubmit();
	}

	protected function onAfterSubmit()
	{
		if (isset($this->afterSubmit) && is_callable($this->afterSubmit)) {
			call_user_func($this->afterSubmit);
		} else {
			$redirectUrl = Yii::app()->createUrl($this->controller->getUniqueId());
			$this->controller->redirect($redirectUrl);
		}
	}

	protected function submitSave()
	{
		foreach ($this->models as $varName => $params) {
			!$params['pk'] ? $params['pk'] = 'id' : null;
			if (isset($_POST[$params['class']])) {
				if (!$model = $this->loadModel($params['class'], 'POST', $params['pk']))
					$model = $this->loadModel($params['class'], null, $params['pk']);
				if (array_key_exists('scenario', $params))
					$model->setScenario($params['scenario']);
				$model->setAttributes($_POST[$params['class']]);
				if (isset($params['attributes']) && is_array($params['attributes']))
					foreach ($params['attributes'] as $attribute => $value) {
						if (is_callable($value))
							$value = call_user_func($value, $this->models);
						$model->$attribute = $value;
					}
				if ($model->save()) {
					$this->_models[$varName]['model'] = $model;
					$this->notify('save-success');
				} else throw new CHttpException(409, $params['class'] . ' save failed.');
			}
		}
	}

	protected function submitDelete()
	{
		foreach ($this->models as $params) {
			!$params['pk'] ? $params['pk'] = 'id' : null;
			$model = $this->loadModel($params['class'], 'POST', $params['pk']);
			if ($model instanceof CActiveRecord) {
				if ($model->delete())
					$this->notify('delete-success');
				else throw new CHttpException(409, $params['class'] . ' delete failed.');
			}
		}
	}

	protected function loadModel($modelName, $source = null, $pk = 'id')
	{
		if (class_exists($modelName)) {

			if (!isset($source))
				return new $modelName();

			$source = strtoupper($source);

			if (in_array($source, ['GET', 'POST'])) {
				$source = eval('return $_' . $source . ';');

				if (!is_array($pk))
					$pk = [$pk];

				$keys = [];
				foreach ($pk as $key) {
					if (isset($source[$modelName][$key]))
						$keys[$key] = $source[$modelName][$key];
				}

				return $modelName::model()->findByPk($keys);

			}

		}
		return null;
	}

	private function notify($label)
	{
		switch ($label) {
			case 'delete-success':
				Yii::app()->user->setFlash(rand(), CJSON::encode([
					'title' => Yii::t('qadmin', 'Success'),
					'type' => 'success',
					'text' => Yii::t('qadmin', 'Deleted successfully.'),
				]));
				break;
			case 'save-success':
				Yii::app()->user->setFlash(rand(), CJSON::encode([
					'title' => Yii::t('qadmin', 'Success'),
					'type' => 'success',
					'text' => Yii::t('qadmin', 'Saved successfully.'),
				]));
				break;
		}
	}
}
