<?php
/**
 * @var \lib\Repositories\TranslationRepository $lang
 * @var bool $isHttps
 */

?>
<div id="Loading" class="view"><?= $lang->translate('LOADING') ?></div>

<div id="Login" class="cover-image">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <div class="col-md-4">&nbsp;</div>
            <div class="col-md-4 my-auto">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title m-0"><?= $lang->translate('LOGIN') ?></h4>
                    </div>
                    <div id="LoginAlerts">
                        <? if (!$isHttps): ?>
                        <div class="card-body">
                            <div class="alert alert-warning" role="alert">
                                <strong><?= $lang->translate('LOGIN_HTTPS_WARNING') ?></strong>
                            </div>
                        </div>
                        <? endif; ?>
                        <div class="card-body" id="LoginMessage">
                            <div class="alert alert-danger" role="alert"></div>
                        </div>
                    </div>
                    <div class="card-body p-0" id="LoginInputs">
                        <input type="text" id="LoginUsername" placeholder="<?= $lang->translate('USERNAME') ?>">
                        <input type="password" id="LoginPassword" placeholder="<?= $lang->translate('PASSWORD') ?>">
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-sm float-right" id="LoginButton"><?= $lang->translate('LOGIN') ?></button>
                    </div>

                </div>
            </div>
            <div class="col-md-4">&nbsp;</div>
        </div>
    </div>
</div>
