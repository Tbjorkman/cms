<div class="form-group">
	<label for="title"><?php echo TITLE ?>:</label>
	<input type="text" name="title" id="title" class="form-control" required maxlength="55" autofocus value="<?php echo $title ?>">
</div>

<div class="form-group">
	<label for="url_key"><?php echo URL_KEY ?>:</label>
	<input type="text" name="url_key" id="url_key" class="form-control" required maxlength="55" pattern="([a-z0-9-.])+" title="<?php echo INVALID_URL_KEY ?>" value="<?php echo $url_key ?>">
</div>

<div class="form-group">
	<label for="content"><?php echo CONTENT ?>:</label>
	<textarea name="content" id="content"><?php echo $content ?></textarea>
	<script>
		CKEDITOR.replace('content', {
			toolbar: 'Full'
		})
	</script>
</div>

<div class="form-group">
	<label for="meta_description"><?php echo META_DESCRIPTION ?>: <small class="text-muted">(<?php echo OPTIONAL ?>)</small></label>
	<textarea name="meta_description" id="meta_description" class="form-control" maxlength="155"><?php echo $meta_description_tmp ?></textarea>
</div>

<button type="submit" class="<?php echo $buttons['save'] ?>" name="save_item">
	<?php echo $icons['save'] . ' ' .SAVE_CHANGES ?>
</button>