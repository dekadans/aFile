class LoginPanel extends React.Component {
    render() {
        return (
            <div className="panel panel-default">
                <div className="panel-heading">
                    <h3 className="panel-title">LOADING</h3>
                </div>
                <div className="panel-body" id="LoginMessage">
                    <div className="alert alert-danger" role="alert"></div>
                </div>
                <div id="LoginInputs">
                    <input type="text" id="LoginUsername" placeholder="USERNAME" />
                    <input type="password" id="LoginPassword" placeholder="PASSWORD" />
                </div>
                <div className="panel-footer">
                    <button className="btn btn-primary btn-sm right" id="LoginButton">LOGIN</button>
                    <div className="clearfix"></div>
                </div>
            </div>
        );
    }
}


ReactDOM.render(
    <LoginPanel />,
    document.getElementById('LoginContainer')
);
