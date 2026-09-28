    <section class="intro">
      <p class="eyebrow">The story behind the portal</p>
      <h1>The Curing Process</h1>
      <p class="tagline">"This is the curing process and like all transformative experiences it will take time." - Nivlema</p>
    </section>

    <section class="panel" aria-labelledby="bio-heading">
      <h2 id="bio-heading">Meet <?= htmlspecialchars($artist['name'], ENT_QUOTES, 'UTF-8') ?></h2>
      <?php foreach ($artist['bio'] as $paragraph): ?>
        <p><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    </section>

    <section aria-labelledby="cast-heading">
      <h2 id="cast-heading">Figures of Yoo-U</h2>
      <div class="card-grid">
        <?php foreach ($characters as $character): ?>
          <article class="info-card">
            <p class="eyebrow"><?= htmlspecialchars($character['role'], ENT_QUOTES, 'UTF-8') ?></p>
            <h3><?= htmlspecialchars($character['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($character['text'], ENT_QUOTES, 'UTF-8') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="panel" aria-labelledby="oath-heading">
      <h2 id="oath-heading">The Lightbearer Oath</h2>
      <ol class="oath-list">
        <?php foreach ($oath as $line): ?>
          <li><?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ol>
    </section>

    <section aria-labelledby="music-heading">
      <h2 id="music-heading">Music from the Story</h2>
      <p>Every song below begins at a turning point in the story. The <strong>Music EP</strong> in the Offerings carries this sound from the Lightbearer archives.</p>
      <table class="admin-table track-table">
        <caption class="visually-hidden">Songs from The Curing Process</caption>
        <thead>
          <tr><th scope="col">#</th><th scope="col">Track</th><th scope="col">Where it plays</th></tr>
        </thead>
        <tbody>
          <?php foreach ($tracks as $i => $track): ?>
            <tr>
              <td><?= (int) $i + 1 ?></td>
              <td><?= htmlspecialchars($track['title'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($track['moment'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section aria-labelledby="soon-heading">
      <h2 id="soon-heading">Coming Soon</h2>
      <div class="card-grid">
        <?php foreach ($comingSoon as $item): ?>
          <article class="info-card soon">
            <p class="eyebrow">In development</p>
            <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="cta-panel" aria-labelledby="cta-heading">
      <h2 id="cta-heading">Begin your own curing process</h2>
      <p>Join the Shining Light Army and carry a piece of Donny's journey with you.</p>
      <a href="<?= \App\Core\Url::to('offerings') ?>" class="cta-button">Browse the Offerings</a>
    </section>
