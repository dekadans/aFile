class aFileNavigation {
    constructor() {
        if (typeof window.sessionStorage.aFile_Navigation_State !== 'undefined') {
            let savedState = JSON.parse(window.sessionStorage.aFile_Navigation_State);
            this.loadState(savedState);
        } else {
            this.state = {
                path : [],
                search : ''
            };
        }
        history.replaceState(this.state, '');
    }

    pushDirectory(directory) {
        this.state.path.push(directory);
        this.saveState();
    }

    getCurrentLocation() {
        let pathLength = this.state.path.length;

        if (pathLength > 0) {
            let lastDirectoryInPath = this.state.path[pathLength-1];
            return lastDirectoryInPath.id;
        }

        return null;
    }

    getPathStack() {
        return this.state.path;
    }

    setSearchString(searchString) {
        this.state.search = searchString;
        this.saveState();
    }

    getSearchString() {
        return this.state.search;
    }

    isSearching() {
        return (this.state.search !== '');
    }

    isAtRoot() {
        return (this.state.path.length === 0);
    }

    goToRoot() {
        this.state = {
            path : [],
            search : ''
        };
        this.saveState();
    }

    saveState() {
        history.pushState(this.state, '');
        window.sessionStorage.setItem('aFile_Navigation_State', JSON.stringify(this.state));
    }

    loadState(state) {
        if (state !== null) {
            this.state = state;
            window.sessionStorage.setItem('aFile_Navigation_State', JSON.stringify(this.state));
        }
    }
}