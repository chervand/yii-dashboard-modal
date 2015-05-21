```
/** @var YDModalWidget $modal */
$modal = $this->widget('YDModalWidget', [
	'actions' => [
		'edit' => $this->createUrl('modalEdit'),
		'delete' => $this->createUrl('modalDelete'),
	],
]);
```


```
...
'columns' => [
		...
		[
			'type' => 'raw',
			'htmlOptions' => ['class' => 'grid-icon'],
			'value' => function ($model) use ($modal) {
				return $modal->link(
					'<i class="fa fa-trash-o text-danger"></i>',
					$modal->createActionUrl('deleteMenuItem', $model, 'name'),
					['title' => Yii::t('app', 'Delete')]
				);
			}
		],
		...
	],
...
```

```
public function actions()
	{
		return [
			'modalEdit' => [
				'class' => 'YDModalActionEdit',
				'modalView' => 'menu/_modalEdit',
				'formId' => 'form-edit',
				'models' => [
					'model' => [
						'class' => 'Model',
						'pk' => 'id'
					],
				],
				'afterSubmit' => function () {
                    $this->redirect('afterSave');
                }
			],
			'modalDelete' => [
				'class' => 'YDModalActionDelete',
				'modalView' => '_modalDelete',
				'formId' => 'form-delete',
				'models' => [
					'model' => [
						'class' => 'Model',
						'pk' => ['id1', 'id2', 'id3']
					],
				],
				'afterSubmit' => function () {
                    $this->redirect('afterDelete');
                }
			],
		];
	}
```

req YDActiveForm