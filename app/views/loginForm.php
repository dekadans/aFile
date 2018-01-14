<div id="Loading" class="view"><?= $L['LOADING'] ?></div>

<div id="Login" class="view">
    <div id="LoginSplash">
        <div id="LoginCenter">
            <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= $L['LOGIN'] ?></h3>
                    </div>
                    <div class="panel-body" id="LoginMessage">
                        <div class="alert alert-danger" role="alert"></div>
                    </div>
                    <div id="LoginInputs">
                        <input type="text" id="LoginUsername" placeholder="<?= $L['USERNAME'] ?>">
                        <input type="password" id="LoginPassword" placeholder="<?= $L['PASSWORD'] ?>">
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary btn-sm right" id="LoginButton"><?= $L['LOGIN'] ?></button>
                        <div class="clearfix"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
