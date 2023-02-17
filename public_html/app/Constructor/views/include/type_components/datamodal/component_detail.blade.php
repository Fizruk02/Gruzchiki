<?php
$table = $form['datamodal_table'];
$field = explode(',', $form['datamodal_columns'])[0];
echo @BTBooster::first($table, ['id' => $value])->$field;
?>
