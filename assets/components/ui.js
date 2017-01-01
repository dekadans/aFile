class UI extends React.Component {
    constructor() {
        super();
        this.state = {
            login : false,
            loginMessage : '',
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
                loading : false,
                loginMessage : ''
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
                        loginMessage : APP.l[data.error]
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
                    <LoginScreen login={(username, password) => this.login(username, password)} loginMessage={this.state.loginMessage} />
                </div>
            );
        }
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
