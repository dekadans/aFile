class MainScreen extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            files : [],
            path : []
        };

        this.fetch();
    }

    fetch() {
        var path = '/' + this.state.path.join('/');
        path = btoa(path);
        $.getJSON('app/api.php',{do : 'ListFiles', location : path}, data => {
            this.setState({
                files : data
            });
        });
    }

    render () {
        return (
            <div id="Main">
                <NavBar logout={this.props.logout} />
                <div id="Menu">
                    <div className="container">
                        <div className="row">
                            <div className="col-md-12">
                                <Breadcrumbs />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12">
                                <div className="btn-group">
                                    <MenuButton icon="glyphicon-trash" />
                                    <MenuButton icon="glyphicon-edit" />
                                    <MenuButton icon="glyphicon-share" />
                                    <MenuButton icon="glyphicon-download-alt" />
                                </div>
                                <div className="btn-group pull-right">
                                    <MenuButton icon="glyphicon-folder-open" />
                                    <MenuButton icon="glyphicon-link" />
                                    <MenuButton icon="glyphicon-font" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="container">
                    <FileList files={this.state.files} />
                </div>
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

class Breadcrumbs extends React.Component {
    render() {
        return (
            <ol className="breadcrumb">
                <li>{APP.l.HOME}</li>
            </ol>
        );
    }
}

class MenuButton extends React.Component {
    render() {
        return (
            <button type="button" className="btn btn-default"><span className={"glyphicon " + this.props.icon}></span></button>
        );
    }
}
