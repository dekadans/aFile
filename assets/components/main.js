class MainScreen extends React.Component {
    constructor(props) {
        super(props);
        this.setActiveFile = this.setActiveFile.bind(this);
        this.drag = this.drag.bind(this);
        this.dropFile = this.dropFile.bind(this);
        this.state = {
            files : [],
            path : [],
            activeFile : -1
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

    setActiveFile(index) {
        this.setState({
            activeFile : index
        });
    }

    drag(event) {
        // Split this and do more stuff
        event.preventDefault();
        event.stopPropagation();
    }

    dropFile(event) {
        event.preventDefault();
        event.stopPropagation();
        var filesToUpload = event.dataTransfer.files;

        var ajaxData = new FormData();

        if (filesToUpload) {
            $.each( filesToUpload, function(i, file) {
                ajaxData.append('fileinput', file );
            });
        }

        var path = '/' + this.state.path.join('/');
        path = btoa(path);

        $.ajax({
            url: 'app/api.php?do=Upload&location=' + path,
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            complete: function() {
                console.log('complete');
            },
            success: function(data) {
                console.log('success');
            },
            error: function() {
                console.log('error');
            }
        });
    }

    render () {
        return (
            <div id="Main" onDragStart={this.drag} onDragEnter={this.drag} onDragOver={this.drag} onDragLeave={this.drag} onDrop={this.dropFile}>
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
                                    <MenuButton icon="glyphicon-trash" action="DELETE" files={this.state.files} activeFile={this.state.activeFile} />
                                    <MenuButton icon="glyphicon-edit" action="EDIT" files={this.state.files} activeFile={this.state.activeFile} />
                                    <MenuButton icon="glyphicon-share" action="SHARE" files={this.state.files} activeFile={this.state.activeFile} />
                                    <MenuButton icon="glyphicon-download-alt" action="DOWNLOAD" files={this.state.files} activeFile={this.state.activeFile} />
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
                    <FileList files={this.state.files} activeFile={this.state.activeFile} fileCallback={this.setActiveFile} />
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
    constructor(props) {
        super(props);
        this.buttonClick = this.buttonClick.bind(this);
    }

    buttonClick() {
        if (this.props.activeFile > -1) {
            console.log(this.props.action + ' ' + this.props.files[this.props.activeFile]['name']);
        }
    }

    render() {
        var disabled = false;

        if (this.props.activeFile && this.props.activeFile === -1) {
            var disabled = true;
        }

        return (
            <button type="button" onClick={this.buttonClick} disabled={disabled} className="btn btn-default"><span className={"glyphicon " + this.props.icon}></span></button>
        );
    }
}
