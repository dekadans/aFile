"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var LoginPanel = function (_React$Component) {
    _inherits(LoginPanel, _React$Component);

    function LoginPanel() {
        _classCallCheck(this, LoginPanel);

        return _possibleConstructorReturn(this, (LoginPanel.__proto__ || Object.getPrototypeOf(LoginPanel)).apply(this, arguments));
    }

    _createClass(LoginPanel, [{
        key: "render",
        value: function render() {
            return React.createElement(
                "div",
                { className: "panel panel-default" },
                React.createElement(
                    "div",
                    { className: "panel-heading" },
                    React.createElement(
                        "h3",
                        { className: "panel-title" },
                        "LOADING"
                    )
                ),
                React.createElement(
                    "div",
                    { className: "panel-body", id: "LoginMessage" },
                    React.createElement("div", { className: "alert alert-danger", role: "alert" })
                ),
                React.createElement(
                    "div",
                    { id: "LoginInputs" },
                    React.createElement("input", { type: "text", id: "LoginUsername", placeholder: "USERNAME" }),
                    React.createElement("input", { type: "password", id: "LoginPassword", placeholder: "PASSWORD" })
                ),
                React.createElement(
                    "div",
                    { className: "panel-footer" },
                    React.createElement(
                        "button",
                        { className: "btn btn-primary btn-sm right", id: "LoginButton" },
                        "LOGIN"
                    ),
                    React.createElement("div", { className: "clearfix" })
                )
            );
        }
    }]);

    return LoginPanel;
}(React.Component);

ReactDOM.render(React.createElement(LoginPanel, null), document.getElementById('LoginContainer'));
