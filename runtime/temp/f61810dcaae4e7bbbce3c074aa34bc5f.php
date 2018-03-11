<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:58:"/web/tp5/public/../application/index/view/index/index.html";i:1520778082;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>fefe</title>
    <!-- <link rel="stylesheet" href=""> -->
</head>
<body>
    this is test ! welcome <?php echo $name; ?>
    <?php echo time();?>
    <?php echo (isset($username) && ($username !== '')?$username:"heige"); ?>
    <form action="<?php echo url('index/upload'); ?>" enctype="multipart/form-data" method="post">
        <input type="file" name="image" /> <br>
        <input type="submit" value="上传" />
    </form>
    <div><img src="<?php echo captcha_src(); ?>" alt="captcha" /></div>
</body>
</html>
