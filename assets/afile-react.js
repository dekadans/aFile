/**
 * REACT COMPONENTS
 */

class App extends React.Component {
    constructor() {
        super();
        this.state = {
            login : false
        };

        this.check();
    }

    check() {
        $.getJSON('app/api.php?do=Check', data => {
            APP.init(data);
            this.setState({login : data.login});
        });
    }

    login(username, password) {
        console.log(username, password);
    }

    render () {
        if (this.state.login) {
            return (
                <span>inloggad</span>
            );
        }
        else {
            return (
                <LoginScreen login={(username, password) => this.login(username, password)} />
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
        this.loginClick = this.loginClick.bind(this);
     }

    usernameChange(e) {
        this.setState({username : e.target.value});
    }
    passwordChange(e) {
        this.setState({password : e.target.value});
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
                        <div id="LoginContainer" className="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
                            <div className="panel panel-default">
                                <div className="panel-heading">
                                    <h3 className="panel-title">{APP.l.LOGIN}</h3>
                                </div>
                                <div className="panel-body" id="LoginMessage">
                                    <div className="alert alert-danger" role="alert"></div>
                                </div>
                                <div id="LoginInputs">
                                    <input type="text" id="LoginUsername" onChange={this.usernameChange} value={this.state.username} placeholder={APP.l.USERNAME} />
                                    <input type="password" id="LoginPassword" onChange={this.passwordChange} value={this.state.password} placeholder={APP.l.PASSWORD} />
                                </div>
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

/**
 * aFILE CLASS
 */

class aFile {
    constructor() {
        this.l = {};
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
        <App />,
        document.getElementById('AppContainer')
    );
});
