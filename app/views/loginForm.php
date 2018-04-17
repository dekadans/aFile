<div id="Loading" class="view"><?= $L['LOADING'] ?></div>

<div id="Login">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <div class="col-md-4">&nbsp;</div>
            <div class="col-md-4 my-auto">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title m-0"><?= $L['LOGIN'] ?></h4>
                    </div>
                    <div class="card-body" id="LoginMessage">
                        <div class="alert alert-danger" role="alert"></div>
                    </div>
                    <div class="card-body p-0" id="LoginInputs">
                        <input type="text" id="LoginUsername" placeholder="<?= $L['USERNAME'] ?>">
                        <input type="password" id="LoginPassword" placeholder="<?= $L['PASSWORD'] ?>">
                    </div>
                    <div class="card-footer">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="RememberMe" value="1">
                            <label class="form-check-label" for="RememberMe"><?= $L['REMEMBER_ME'] ?></label>
                        </div>

                        <button class="btn btn-primary btn-sm right" id="LoginButton"><?= $L['LOGIN'] ?></button>
                    </div>

                </div>
            </div>
            <div class="col-md-4">&nbsp;</div>
        </div>
    </div>
</div>
