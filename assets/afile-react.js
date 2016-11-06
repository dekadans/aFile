/**
 * REACT COMPONENTS
 */

class UI extends React.Component {
    constructor() {
        super();
        this.state = {
            login : false,
            loading : false,
            hasChecked : false
        };

        this.check();
    }

    check() {
        $.getJSON('app/api.php?do=Check', data => {
            APP.init(data);
            this.setState({
                hasChecked : true,
                login : data.login,
                loading : false
            });
        });
    }

    login(username, password) {
        if (username.length && password.length) {
            this.setState({loading : true});

            $.post('app/api.php?do=Login', {username : username, password : password}, data => {
                if (data.error) {
                    this.setState({
                        loading : false,
                        errorMessage : APP.l[data.error]
                    });
                }
                else if (data.status == 'ok') {
                    this.check();
                }
            });
        }
    }

    logout () {
        this.setState({loading : true});
        $.getJSON('app/api.php?do=Logout', data => {
            this.check();
        });
    }

    render () {
        if (!this.state.hasChecked) {
            return null;
        }

        if (this.state.login) {
            return (
                <div className="wrap">
                    <Loading show={this.state.loading} />
                    <MainScreen logout={() => this.logout()} />
                </div>
            );
        }
        else {
            return (
                <div className="wrap">
                    <Loading show={this.state.loading} />
                    <LoginScreen login={(username, password) => this.login(username, password)} loginMessage={this.state.errorMessage} />
                </div>
            );
        }
    }
}

class LoginScreen extends React.Component {
    constructor(props) {
        super(props);
        this.state = {username: '', password: ''};
        this.usernameChange = this.usernameChange.bind(this);
        this.passwordChange = this.passwordChange.bind(this);
        this.inputReturn = this.inputReturn.bind(this);
        this.loginClick = this.loginClick.bind(this);
    }

    usernameChange(e) {
        this.setState({username : e.target.value});
    }
    passwordChange(e) {
        this.setState({password : e.target.value});
    }

    inputReturn(e) {
        if (e.keyCode == 13) {
            this.loginClick();
        }
    }

    loginClick() {
        this.props.login(this.state.username, this.state.password);
        this.setState({
            username : '',
            password : ''
        });
    }

    render() {
        return (
            <div id="Login" className="view">
                <div id="LoginSplash">
                    <div id="LoginCenter">
                        <div className="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                            <div className="panel panel-default">
                                <div className="panel-heading">
                                    <h3 className="panel-title">{APP.l.LOGIN}</h3>
                                </div>
                                <LoginMessage text={this.props.loginMessage} />
                                <input type="text" onChange={this.usernameChange} onKeyDown={this.inputReturn} value={this.state.username} placeholder={APP.l.USERNAME} />
                                <input type="password" onChange={this.passwordChange} onKeyDown={this.inputReturn} value={this.state.password} placeholder={APP.l.PASSWORD} />
                                <div className="panel-footer">
                                    <button className="btn btn-primary btn-sm right" onClick={this.loginClick}>{APP.l.LOGIN}</button>
                                    <div className="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

class LoginMessage extends React.Component {
    render () {
        if (this.props.text) {
            return (
                <div className="panel-body" id="LoginMessage">
                    <div className="alert alert-danger" role="alert">{this.props.text}</div>
                </div>
            );
        }
        else {
            return null;
        }
    }
}

class MainScreen extends React.Component {
    render () {
        return (
            <div id="Main">
                <NavBar logout={this.props.logout} />
            </div>
        );
    }
}

class NavBar extends React.Component {
    constructor(props) {
        super(props);
        //this.logoutClick = this.logoutClick.bind(this);
     }

    logoutClick(e) {
        e.preventDefault();
        this.props.logout();
    }

    render() {
        return (
            <nav className="navbar navbar-default navbar-fixed-top">
                <div className="container">
                    <div className="navbar-header">
                        <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#test" aria-expanded="false">
                            <span className="sr-only">Toggle navigation</span>
                            <span className="icon-bar"></span>
                            <span className="icon-bar"></span>
                            <span className="icon-bar"></span>
                        </button>
                        <a href="#" className="navbar-brand">{APP.l.BRAND}</a>
                    </div>
                    <div className="navbar-collapse collapse" id="test" aria-expanded="true">
                        <ul className="nav navbar-nav navbar-right">
                            <li>
                                <a href="#"><span className="glyphicon glyphicon-search" aria-hidden="true"></span> <span className="visible-xs-inline">{APP.l.SEARCH}</span></a>
                            </li>
                            <li className="dropdown">
                                <a href="#" className="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <span className="glyphicon glyphicon-cog" aria-hidden="true"></span> <span className="visible-xs-inline">{APP.l.SETTINGS}</span> <span className="caret"></span>
                                </a>
                                <ul className="dropdown-menu">
                                    <li><a href="#">Something else here</a></li>
                                    <li role="separator" className="divider"></li>
                                    <li><a href="#" onClick={(e) => this.logoutClick(e)}><span className="glyphicon glyphicon-log-out" aria-hidden="true"></span> {APP.l.LOGOUT}</a></li>
                                  </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        );
    }
}

class Loading extends React.Component {
    render() {
        if (this.props.show) {
            return (
                <div id="Loading">{APP.l.LOADING}</div>
            );
        }
        else {
            return null;
        }
    }
}

/**
 * aFILE CLASS
 */

class aFile {
    constructor() {
        this.l = {};
        this.path = [];
    }

    init(checkData) {
        this.l = checkData.language;
        this.siprefix = checkData.siprefix;
    }
}

var APP = null;

$(function(){
    APP = new aFile();

    ReactDOM.render(
        <UI />,
        document.getElementById('AppContainer')
    );
});
