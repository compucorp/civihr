<div class="chr_user-menu">
  <div class="chr_user-menu__data">
    <span class="chr_user-menu__name">{$username}</span>
    <div class="chr_profile-card">
      <div class="chr_profile-card__picture chr_profile-card__picture--small">
        <img src="{$image}" alt="{$username|escape}">
      </div>
    </div>
    <i class="chr_user-menu__arrow fa fa-caret-down"></i>
  </div>
  <nav class="chr_user-menu__dropdown">
    <ul>
      <li>
        <a href="{$editLink}">
          <i class="fa fa-edit"></i>{ts}Edit Account{/ts}
        </a>
      </li>
      <li>
        <a href="http://civihr-documentation.readthedocs.io/en/latest/self-service-portal/login-screen/" target="_blank">
          <i class="fa fa-book"></i>{ts}User guide{/ts}
        </a>
      </li>
      <li>
        <a href="{$logoutLink}">
          <i class="fa fa-sign-out"></i>{ts}Log Out{/ts}
        </a>
      </li>
    </ul>
  </nav>
</div>
