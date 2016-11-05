'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * REACT COMPONENTS
 */

var App = function (_React$Component) {
    _inherits(App, _React$Component);

    function App() {
        _classCallCheck(this, App);

        var _this = _possibleConstructorReturn(this, (App.__proto__ || Object.getPrototypeOf(App)).call(this));

        _this.state = {
            login: false
        };

        _this.check();
        return _this;
    }

    _createClass(App, [{
        key: 'check',
        value: function check() {
            var _this2 = this;

            $.getJSON('app/api.php?do=Check', function (data) {
                APP.init(data);
                _this2.setState({ login: data.login });
            });
        }
    }, {
        key: 'login',
        value: function login(username, password) {
            console.log(username, password);
        }
    }, {
        key: 'render',
        value: function render() {
            var _this3 = this;

            console.log(APP);
            //this.check();
            if (this.state.login) {
                return React.createElement(
                    'span',
                    null,
                    'inloggad'
                );
            } else {
                return React.createElement(LoginScreen, { login: function login(username, password) {
                        return _this3.login(username, password);
                    } });
            }
        }
    }]);

    return App;
}(React.Component);

var LoginScreen = function (_React$Component2) {
    _inherits(LoginScreen, _React$Component2);

    function LoginScreen(props) {
        _classCallCheck(this, LoginScreen);

        var _this4 = _possibleConstructorReturn(this, (LoginScreen.__proto__ || Object.getPrototypeOf(LoginScreen)).call(this, props));

        _this4.state = { username: '', password: '' };
        _this4.usernameChange = _this4.usernameChange.bind(_this4);
        _this4.passwordChange = _this4.passwordChange.bind(_this4);
        _this4.loginClick = _this4.loginClick.bind(_this4);
        return _this4;
    }

    _createClass(LoginScreen, [{
        key: 'usernameChange',
        value: function usernameChange(e) {
            this.setState({ username: e.target.value });
        }
    }, {
        key: 'passwordChange',
        value: function passwordChange(e) {
            this.setState({ password: e.target.value });
        }
    }, {
        key: 'loginClick',
        value: function loginClick() {
            this.props.login(this.state.username, this.state.password);
            this.setState({
                username: '',
                password: ''
            });
        }
    }, {
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                { id: 'Login', className: 'view' },
                React.createElement(
                    'div',
                    { id: 'LoginSplash' },
                    React.createElement(
                        'div',
                        { id: 'LoginCenter' },
                        React.createElement(
                            'div',
                            { id: 'LoginContainer', className: 'col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3' },
                            React.createElement(
                                'div',
                                { className: 'panel panel-default' },
                                React.createElement(
                                    'div',
                                    { className: 'panel-heading' },
                                    React.createElement(
                                        'h3',
                                        { className: 'panel-title' },
                                        APP.l.LOGIN
                                    )
                                ),
                                React.createElement(
                                    'div',
                                    { className: 'panel-body', id: 'LoginMessage' },
                                    React.createElement('div', { className: 'alert alert-danger', role: 'alert' })
                                ),
                                React.createElement(
                                    'div',
                                    { id: 'LoginInputs' },
                                    React.createElement('input', { type: 'text', id: 'LoginUsername', onChange: this.usernameChange, value: this.state.username, placeholder: APP.l.USERNAME }),
                                    React.createElement('input', { type: 'password', id: 'LoginPassword', onChange: this.passwordChange, value: this.state.password, placeholder: APP.l.PASSWORD })
                                ),
                                React.createElement(
                                    'div',
                                    { className: 'panel-footer' },
                                    React.createElement(
                                        'button',
                                        { className: 'btn btn-primary btn-sm right', onClick: this.loginClick },
                                        APP.l.LOGIN
                                    ),
                                    React.createElement('div', { className: 'clearfix' })
                                )
                            )
                        )
                    )
                )
            );
        }
    }]);

    return LoginScreen;
}(React.Component);

/**
 * aFILE CLASS
 */

var aFile = function () {
    function aFile() {
        _classCallCheck(this, aFile);

        this.l = {};
    }

    _createClass(aFile, [{
        key: 'init',
        value: function init(checkData) {
            this.l = checkData.language;
            this.siprefix = checkData.siprefix;
        }
    }]);

    return aFile;
}();

var APP = null;

$(function () {
    APP = new aFile();

    ReactDOM.render(React.createElement(App, null), document.getElementById('AppContainer'));
});
