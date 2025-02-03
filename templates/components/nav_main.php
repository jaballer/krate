<?php
// type of array
$navItems = [

        ['href' => '/contact-us.php', 'label' => 'Contact Us'],
    ];
?>

<ul class="navbar-nav ml-auto mr-md-2">
    <?php foreach ($navItems as $item): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= htmlspecialchars($item['href']) ?>"><?= htmlspecialchars($item['label']) ?></a>
        </li>
    <?php endforeach; ?>
</ul>