class aFileNavigation {
    constructor() {
        this.state = {};
        this.reset();
    }

    pushDirectory(/* aFileDirectory */ directory) {
        this.state.path.push(directory);
    }

    popDirectory() {
        return this.state.path.pop();
    }

    getCurrentLocation() {
        let pathLength = this.state.path.length;

        if (pathLength > 0) {
            let lastDirectoryInPath = this.state.path[pathLength-1];
            return lastDirectoryInPath.getId();
        }

        return null;
    }

    getPathStack() {
        return this.state.path;
    }

    setPathStack(stack) {
        this.state.path = stack;
    }

    setSearchString(searchString) {
        this.state.search = searchString;
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

    reset() {
        this.state = {
            path : [],
            search : ''
        };
    }
}

class aFileDirectory {
    constructor(id, name) {
        this.id = id;
        this.name = name;
    }

    getId() {
        return this.id;
    }

    getName() {
        return this.name;
    }
}