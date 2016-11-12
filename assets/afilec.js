'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * REACT COMPONENTS
 */

var UI = function (_React$Component) {
    _inherits(UI, _React$Component);

    function UI() {
        _classCallCheck(this, UI);

        var _this = _possibleConstructorReturn(this, (UI.__proto__ || Object.getPrototypeOf(UI)).call(this));

        _this.state = {
            login: false,
            loading: false,
            hasChecked: false
        };

        _this.check();
        return _this;
    }

    _createClass(UI, [{
        key: 'check',
        value: function check() {
            var _this2 = this;

            $.getJSON('app/api.php?do=Check', function (data) {
                APP.init(data);
                _this2.setState({
                    hasChecked: true,
                    login: data.login,
                    loading: false
                });
            });
        }
    }, {
        key: 'login',
        value: function login(username, password) {
            var _this3 = this;

            if (username.length && password.length) {
                this.setState({ loading: true });

                $.post('app/api.php?do=Login', { username: username, password: password }, function (data) {
                    if (data.error) {
                        _this3.setState({
                            loading: false,
                            errorMessage: APP.l[data.error]
                        });
                    } else if (data.status == 'ok') {
                        _this3.check();
                    }
                });
            }
        }
    }, {
        key: 'logout',
        value: function logout() {
            var _this4 = this;

            this.setState({ loading: true });
            $.getJSON('app/api.php?do=Logout', function (data) {
                _this4.check();
            });
        }
    }, {
        key: 'render',
        value: function render() {
            var _this5 = this;

            if (!this.state.hasChecked) {
                return null;
            }

            if (this.state.login) {
                return React.createElement(
                    'div',
                    { className: 'wrap' },
                    React.createElement(Loading, { show: this.state.loading }),
                    React.createElement(MainScreen, { logout: function logout() {
                            return _this5.logout();
                        } })
                );
            } else {
                return React.createElement(
                    'div',
                    { className: 'wrap' },
                    React.createElement(Loading, { show: this.state.loading }),
                    React.createElement(LoginScreen, { login: function login(username, password) {
                            return _this5.login(username, password);
                        }, loginMessage: this.state.errorMessage })
                );
            }
        }
    }]);

    return UI;
}(React.Component);

var LoginScreen = function (_React$Component2) {
    _inherits(LoginScreen, _React$Component2);

    function LoginScreen(props) {
        _classCallCheck(this, LoginScreen);

        var _this6 = _possibleConstructorReturn(this, (LoginScreen.__proto__ || Object.getPrototypeOf(LoginScreen)).call(this, props));

        _this6.state = { username: '', password: '' };
        _this6.usernameChange = _this6.usernameChange.bind(_this6);
        _this6.passwordChange = _this6.passwordChange.bind(_this6);
        _this6.inputReturn = _this6.inputReturn.bind(_this6);
        _this6.loginClick = _this6.loginClick.bind(_this6);
        return _this6;
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
        key: 'inputReturn',
        value: function inputReturn(e) {
            if (e.keyCode == 13) {
                this.loginClick();
            }
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
                { id: 'Login' },
                React.createElement(
                    'div',
                    { id: 'LoginSplash' },
                    React.createElement(
                        'div',
                        { id: 'LoginCenter' },
                        React.createElement(
                            'div',
                            { className: 'col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3' },
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
                                React.createElement(LoginMessage, { text: this.props.loginMessage }),
                                React.createElement('input', { type: 'text', onChange: this.usernameChange, onKeyDown: this.inputReturn, value: this.state.username, placeholder: APP.l.USERNAME }),
                                React.createElement('input', { type: 'password', onChange: this.passwordChange, onKeyDown: this.inputReturn, value: this.state.password, placeholder: APP.l.PASSWORD }),
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

var LoginMessage = function (_React$Component3) {
    _inherits(LoginMessage, _React$Component3);

    function LoginMessage() {
        _classCallCheck(this, LoginMessage);

        return _possibleConstructorReturn(this, (LoginMessage.__proto__ || Object.getPrototypeOf(LoginMessage)).apply(this, arguments));
    }

    _createClass(LoginMessage, [{
        key: 'render',
        value: function render() {
            if (this.props.text) {
                return React.createElement(
                    'div',
                    { className: 'panel-body', id: 'LoginMessage' },
                    React.createElement(
                        'div',
                        { className: 'alert alert-danger', role: 'alert' },
                        this.props.text
                    )
                );
            } else {
                return null;
            }
        }
    }]);

    return LoginMessage;
}(React.Component);

var MainScreen = function (_React$Component4) {
    _inherits(MainScreen, _React$Component4);

    function MainScreen() {
        _classCallCheck(this, MainScreen);

        return _possibleConstructorReturn(this, (MainScreen.__proto__ || Object.getPrototypeOf(MainScreen)).apply(this, arguments));
    }

    _createClass(MainScreen, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                { id: 'Main' },
                React.createElement(NavBar, { logout: this.props.logout }),
                React.createElement(
                    'div',
                    { id: 'Menu' },
                    React.createElement(
                        'div',
                        { className: 'container' },
                        React.createElement(
                            'div',
                            { className: 'row' },
                            React.createElement(
                                'div',
                                { className: 'col-md-12' },
                                React.createElement(Breadcrumbs, null)
                            )
                        ),
                        React.createElement(
                            'div',
                            { className: 'row' },
                            React.createElement(
                                'div',
                                { className: 'col-md-12' },
                                React.createElement(
                                    'div',
                                    { className: 'btn-group' },
                                    React.createElement(MenuButton, { icon: 'glyphicon-trash' }),
                                    React.createElement(MenuButton, { icon: 'glyphicon-edit' }),
                                    React.createElement(MenuButton, { icon: 'glyphicon-share' }),
                                    React.createElement(MenuButton, { icon: 'glyphicon-download-alt' })
                                ),
                                React.createElement(
                                    'div',
                                    { className: 'btn-group pull-right' },
                                    React.createElement(MenuButton, { icon: 'glyphicon-folder-open' }),
                                    React.createElement(MenuButton, { icon: 'glyphicon-link' }),
                                    React.createElement(MenuButton, { icon: 'glyphicon-font' })
                                )
                            )
                        )
                    )
                )
            );
        }
    }]);

    return MainScreen;
}(React.Component);

var NavBar = function (_React$Component5) {
    _inherits(NavBar, _React$Component5);

    function NavBar(props) {
        _classCallCheck(this, NavBar);

        return _possibleConstructorReturn(this, (NavBar.__proto__ || Object.getPrototypeOf(NavBar)).call(this, props));
        //this.logoutClick = this.logoutClick.bind(this);
    }

    _createClass(NavBar, [{
        key: 'logoutClick',
        value: function logoutClick(e) {
            e.preventDefault();
            this.props.logout();
        }
    }, {
        key: 'render',
        value: function render() {
            var _this10 = this;

            return React.createElement(
                'nav',
                { className: 'navbar navbar-default navbar-fixed-top' },
                React.createElement(
                    'div',
                    { className: 'container' },
                    React.createElement(
                        'div',
                        { className: 'navbar-header' },
                        React.createElement(
                            'button',
                            { type: 'button', className: 'navbar-toggle collapsed', 'data-toggle': 'collapse', 'data-target': '#test', 'aria-expanded': 'false' },
                            React.createElement(
                                'span',
                                { className: 'sr-only' },
                                'Toggle navigation'
                            ),
                            React.createElement('span', { className: 'icon-bar' }),
                            React.createElement('span', { className: 'icon-bar' }),
                            React.createElement('span', { className: 'icon-bar' })
                        ),
                        React.createElement(
                            'a',
                            { href: '#', className: 'navbar-brand' },
                            APP.l.BRAND
                        )
                    ),
                    React.createElement(
                        'div',
                        { className: 'navbar-collapse collapse', id: 'test', 'aria-expanded': 'true' },
                        React.createElement(
                            'ul',
                            { className: 'nav navbar-nav navbar-right' },
                            React.createElement(
                                'li',
                                null,
                                React.createElement(
                                    'a',
                                    { href: '#' },
                                    React.createElement('span', { className: 'glyphicon glyphicon-search', 'aria-hidden': 'true' }),
                                    ' ',
                                    React.createElement(
                                        'span',
                                        { className: 'visible-xs-inline' },
                                        APP.l.SEARCH
                                    )
                                )
                            ),
                            React.createElement(
                                'li',
                                { className: 'dropdown' },
                                React.createElement(
                                    'a',
                                    { href: '#', className: 'dropdown-toggle', 'data-toggle': 'dropdown', role: 'button', 'aria-haspopup': 'true', 'aria-expanded': 'false' },
                                    React.createElement('span', { className: 'glyphicon glyphicon-cog', 'aria-hidden': 'true' }),
                                    ' ',
                                    React.createElement(
                                        'span',
                                        { className: 'visible-xs-inline' },
                                        APP.l.SETTINGS
                                    ),
                                    ' ',
                                    React.createElement('span', { className: 'caret' })
                                ),
                                React.createElement(
                                    'ul',
                                    { className: 'dropdown-menu' },
                                    React.createElement(
                                        'li',
                                        null,
                                        React.createElement(
                                            'a',
                                            { href: '#' },
                                            'Something else here'
                                        )
                                    ),
                                    React.createElement('li', { role: 'separator', className: 'divider' }),
                                    React.createElement(
                                        'li',
                                        null,
                                        React.createElement(
                                            'a',
                                            { href: '#', onClick: function onClick(e) {
                                                    return _this10.logoutClick(e);
                                                } },
                                            React.createElement('span', { className: 'glyphicon glyphicon-log-out', 'aria-hidden': 'true' }),
                                            ' ',
                                            APP.l.LOGOUT
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            );
        }
    }]);

    return NavBar;
}(React.Component);

var Breadcrumbs = function (_React$Component6) {
    _inherits(Breadcrumbs, _React$Component6);

    function Breadcrumbs() {
        _classCallCheck(this, Breadcrumbs);

        return _possibleConstructorReturn(this, (Breadcrumbs.__proto__ || Object.getPrototypeOf(Breadcrumbs)).apply(this, arguments));
    }

    _createClass(Breadcrumbs, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'ol',
                { className: 'breadcrumb' },
                React.createElement(
                    'li',
                    null,
                    APP.l.HOME
                )
            );
        }
    }]);

    return Breadcrumbs;
}(React.Component);

var MenuButton = function (_React$Component7) {
    _inherits(MenuButton, _React$Component7);

    function MenuButton() {
        _classCallCheck(this, MenuButton);

        return _possibleConstructorReturn(this, (MenuButton.__proto__ || Object.getPrototypeOf(MenuButton)).apply(this, arguments));
    }

    _createClass(MenuButton, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'button',
                { type: 'button', className: 'btn btn-default' },
                React.createElement('span', { className: "glyphicon " + this.props.icon })
            );
        }
    }]);

    return MenuButton;
}(React.Component);

var Loading = function (_React$Component8) {
    _inherits(Loading, _React$Component8);

    function Loading() {
        _classCallCheck(this, Loading);

        return _possibleConstructorReturn(this, (Loading.__proto__ || Object.getPrototypeOf(Loading)).apply(this, arguments));
    }

    _createClass(Loading, [{
        key: 'render',
        value: function render() {
            if (this.props.show) {
                return React.createElement(
                    'div',
                    { id: 'Loading' },
                    APP.l.LOADING
                );
            } else {
                return null;
            }
        }
    }]);

    return Loading;
}(React.Component);

/**
 * aFILE CLASS
 */

var aFile = function () {
    function aFile() {
        _classCallCheck(this, aFile);

        this.l = {};
        this.path = [];
        this.findDefinedFiletypes();
    }

    _createClass(aFile, [{
        key: 'init',
        value: function init(checkData) {
            this.l = checkData.language;
            this.siprefix = checkData.siprefix;
        }

        /**
         * Find which file extentions that has icons defined for them
         */

    }, {
        key: 'findDefinedFiletypes',
        value: function findDefinedFiletypes() {
            this.exts = [];
            for (var i = 0; i < document.styleSheets.length; i++) {
                var name = document.styleSheets[i].href.split('/').pop();
                if (name == 'flaticon.css') {
                    for (var j = 0; j < document.styleSheets[i].rules.length; j++) {
                        var definition = document.styleSheets[i].rules[j].selectorText;
                        if (typeof definition != 'undefined') {
                            if (definition.substring(0, 10) == '.flaticon-') {
                                var ext = definition.substring(10, definition.search(':'));
                                this.exts.push(ext);
                            }
                        }
                    }
                }
            }
        }
    }]);

    return aFile;
}();

var APP = null;

$(function () {
    APP = new aFile();

    ReactDOM.render(React.createElement(UI, null), document.getElementById('AppContainer'));
});
