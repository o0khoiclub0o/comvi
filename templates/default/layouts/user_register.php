		<h1>Register</h1>
		<form method="post" action="">
<?php if (!empty($this->vars['content']->errors)) {?>
			<ul class="errors">
<?php foreach ($this->vars['content']->errors as $error) {?>
				<li><?=$error?></li>
<?php }?>
			</ul>
<?php }?>
			<label>Email <small>(*)</small></label>
			<input type="text" name="email" maxlength="50" />

			<label>Password <small>(*)</small></label>
			<input type="password" name="password" maxlength="32" />

			<label>Fullname <small>(*)</small></label>
			<input type="text" name="fullname" maxlength="50" />

			<label>Website</label>
			<input type="text" name="website" maxlength="50" />

			<label>Twitter</label>
			<input type="text" name="twitter" maxlength="32" />

			<input type="submit" name="submit" value="Register" class="mrT10" />
		</form>
