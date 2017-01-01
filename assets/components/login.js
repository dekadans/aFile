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
            <div id="Login">
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
