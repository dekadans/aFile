class MenuButton extends React.Component {
    constructor(props) {
        super(props);

        this.buttonClick = this.buttonClick.bind(this);
    }

    buttonClick() {
        var file = this.props.files[this.props.activeFile];

        if (this.props.activeFile > -1) {
            switch (this.props.action) {
                case 'DELETE':
                    this.deleteFile(file);
                    break;
            }
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

    // Actions

    deleteFile(file) {
        var buttonReact = this;
        $.getJSON('app/api.php?do=Delete&id='+file.id, function(data) {
            if (data.error) {
                alert(APP.l[data.error]);
            }

            buttonReact.props.fetchCallback();
        });
    }
}
