
class FileList extends React.Component {
    render() {
        var fileJSX = [];

        for (var i = 0; i < this.props.files.length; i++) {
            fileJSX.push(<File fileinfo={this.props.files[i]} key={i} />);
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
    render() {
        var ext = this.props.fileinfo.name.split('.').pop();
        if (APP.exts.indexOf(ext) == -1) {
            ext = 'blank';
        }

        return (
            <tr className="listItem">
                <td><span className={"flaticon-" + ext}></span></td>
                <td>{this.props.fileinfo.name}</td>
                <td>{APP.humanFileSize(this.props.fileinfo.size)}</td>
                <td>{this.props.fileinfo.last_edit}</td>
            </tr>
        );
    }
}
