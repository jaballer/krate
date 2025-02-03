<footer class="site-footer">
    <div class="container text-center">
        <span>&copy; <?php echo date("Y") . "&nbsp;" . $config['site']['owner']; ?></span>
        
        <?php echo displaySocialLinks($settingsManager); ?>
    </div>
</footer>

<?php if ($settingsManager->getSetting('audio_player_on')): ?>
    <?php include __DIR__ . '/../components/audio-player.php'; ?>
<?php endif; ?>

<script src="<?php echo SCRIPTS_PATH; ?>/main.min.js"></script>

</body>
</html>