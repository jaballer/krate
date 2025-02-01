<div id="hero" class="jumbotron jumbotron-fluid px-4">
  <div class="container">
    <h1 class="display-4"><?= $settingsManager->getSetting('site_name'); ?></h1>
    <p class="lead"><?= $settingsManager->getSetting('site_tagline'); ?></p>
    
    <a href="https://github.com/jabaltorres/web-fun/wiki" class="btn btn-secondary" target="_blank">Github Wiki</a>
  </div>
</div>

<div id="content" class="content">
  <div class="row">
    <div class="col-4">
      <h3>Records</h3>
      <p>Bacon ipsum dolor amet turducken jowl flank strip steak pork shank.</p>
      <a href="/records/" class="btn btn-primary">Records</a>
    </div>

    <div class="col-4">
      <h3>Demos</h3>
      <p>Bacon ipsum dolor amet turducken jowl flank strip steak pork shank.</p>
      <a href="/demos/" class="btn btn-primary">Demos</a>
    </div>

    <div class="col-4">
          <h3>Blog</h3>
          <p>Bacon ipsum dolor amet turducken jowl flank strip steak pork shank.</p>
          <a href="/blog/" class="btn btn-primary">Blog</a>
      </div>
  </div>
</div>
