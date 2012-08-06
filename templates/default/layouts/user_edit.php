		<h1>Profile</h1>
		<form method="post" action="">
<?php if (!empty($this->vars['content']->errors)) {?>
			<ul class="errors">
<?php foreach ($this->vars['content']->errors as $error) {?>
				<li><?=$error?></li>
<?php }?>
			</ul>
<?php }?>
			<label>Password</label>
			<input type="password" name="password" maxlength="32" />

			<label>Fullname <small>(*)</small></label>
			<input type="text" name="fullname" maxlength="50" value="<?=$this->vars['content']->item->fullname?>" />

			<label>Website</label>
			<input type="text" name="website" maxlength="50" value="<?=$this->vars['content']->item->params->get('website')?>" />

			<label>Twitter</label>
			<input type="text" name="twitter" maxlength="32" value="<?=$this->vars['content']->item->params->get('twitter')?>" />

			<input type="submit" name="submit" value="Save" class="mrT10" />
		</form>
