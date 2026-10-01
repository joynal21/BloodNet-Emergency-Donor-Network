<?php if(!isset($pageTitle)) $pageTitle='BloodNet'; $u=user(); ?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> | BloodNet</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head><body>
<div class="top-alert"><span class="pulse-dot"></span> <?=e(setting($conn,'announcement','Emergency network active — verified donors & hospitals'))?> <a href="request-blood.php">Request blood now →</a></div>
<header class="nav-wrap"><nav class="nav container">
<a class="brand" href="index.php"><span class="brand-mark">B+</span><span>Blood<span>Net</span><small>Emergency Donor Network</small></span></a>
<button class="nav-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
<div class="nav-links"><a href="index.php">Home</a><a href="requests.php">Requests</a><a href="index.php#how">How it works</a><a href="index.php#hospitals">Hospitals</a>
<?php if($u): ?><a href="dashboard.php">Dashboard</a><a href="history.php">History</a><a class="nav-user" href="profile.php"><span><?=strtoupper(substr($u['name'],0,1))?></span><?=e(explode(' ',$u['name'])[0])?></a><a class="btn btn-sm btn-ghost" href="logout.php">Logout</a>
<?php else: ?><a href="login.php">Login</a><a class="btn btn-sm btn-primary" href="register.php">Join as donor</a><?php endif; ?></div>
</nav></header>
<?php if($m=flash('success')): ?><div class="toast success"><?=e($m)?></div><?php endif; ?>
<?php if($m=flash('error')): ?><div class="toast error"><?=e($m)?></div><?php endif; ?>
<main>
