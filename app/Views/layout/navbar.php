<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <a class="navbar-brand">GEX</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
            <?php
                $nav = array(
                    'class' => 'nav-link'
                );
            ?>
              <li class="nav-item">
              <?= anchor('/', 'Home', $nav); ?>
              </li>
              <li class="nav-item">
                <?= anchor('page1', '--1--', $nav); ?></a>
              </li>
              <li class="nav-item">
                <?= anchor('page2', '--2--', $nav); ?></a>
              </li>
              <li class="nav-item">
                <?= anchor('page3', '--3--', $nav); ?></a>
              </li>
            </ul>
          </div>
        </div>
    </nav>