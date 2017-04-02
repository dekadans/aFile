
class FileList extends React.Component {
    render() {
        var fileJSX = [];

        for (var i = 0; i < this.props.files.length; i++) {
            if (this.props.activeFile == i) {
                var active = true;
            }
            else {
                var active = false;
            }
            fileJSX.push(<File fileinfo={this.props.files[i]} key={i} index={i} active={active} fileCallback={this.props.fileCallback} />);
        }

        return (
            <table id="List">
                <tbody>
                    {fileJSX}
                </tbody>
            </table>
        );
    }
}

class File extends React.Component {
    constructor(props) {
        super(props);
        this.fileClick = this.fileClick.bind(this);
    }

    fileClick() {
        if (this.props.active) {
            this.props.fileCallback(-1);
            return;
        }

        this.props.fileCallback(this.props.index);
    }

    render() {
        var ext = this.props.fileinfo.name.split('.').pop();
        if (APP.exts.indexOf(ext) == -1) {
            ext = 'blank';
        }

        var classes = 'listItem';

        if (this.props.active) {
            classes += ' listItemActive';
        }

        return (
            <tr className={classes} onClick={this.fileClick}>
                <td><span className={"flaticon-" + ext}></span></td>
                <td>{this.props.fileinfo.name}</td>
                <td>{APP.humanFileSize(this.props.fileinfo.size)}</td>
                <td>{this.props.fileinfo.last_edit}</td>
            </tr>
        );
    }
}
