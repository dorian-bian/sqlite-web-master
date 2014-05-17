/*
 *	SQLite Mode for CodeMirror 2
 *	@author Bytes
 *	@version 15/Jan/2013
*/

if(!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(obj, start) {
         for (var i = (start || 0), j = this.length; i < j; i++) {
             if (this[i] === obj) { return i; }
         }
         return -1;
    }
}

CodeMirror.defineMode('sqlite', function(config) {
    var keywords = [
        'ABORT', 'ACTION', 'ADD', 'AFTER', 'ALL', 'ALTER', 'ANALYZE','AND', 'AS', 'ASC', 
        'ATTACH', 'AUTOINCREMENT', 'BEFORE', 'BEGIN', 'BETWEEN','BY', 'CASCADE', 'CASE', 
        'CAST', 'CHECK', 'COLLATE', 'COLUMN', 'COMMIT','CONFLICT', 'CONSTRAINT', 'CREATE', 
        'CROSS','CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'DATABASE', 'DEFAULT', 
        'DEFERRABLE', 'DEFERRED', 'DELETE', 'DESC', 'DETACH', 'DISTINCT', 'DROP', 'EACH', 
        'ELSE', 'END', 'ESCAPE',  'EXCEPT','EXCLUSIVE', 'EXISTS', 'EXPLAIN', 'FAIL', 'FOR', 
        'FOREIGN', 'FROM', 'FULL', 'GLOB', 'GROUP', 'HAVING', 'IF', 'IGNORE', 'IMMEDIATE', 
        'IN', 'INDEX', 'INDEXED', 'INITIALLY', 'INNER', 'INSERT', 'INSTEAD', 'INTERSECT', 
        'INTO', 'IS', 'ISNULL', 'JOIN', 'KEY', 'LEFT', 'LIKE', 'LIMIT', 'MATCH', 'NATURAL', 
        'NO', 'NOT', 'NOTNULL', 'NULL', 'OF', 'OFFSET', 'ON', 'OR', 'ORDER', 'OUTER', 'PLAN', 
        'PRAGMA', 'PRIMARY', 'QUERY', 'RAISE', 'REFERENCES', 'REGEXP', 'REINDEX', 'RELEASE',
        'RENAME', 'REPLACE', 'RESTRICT', 'RIGHT', 'ROLLBACK', 'ROW', 'SAVEPOINT', 'SELECT',
        'SET', 'TABLE','TEMP', 'TEMPORARY', 'THEN', 'TO', 'TRANSACTION', 'TRIGGER', 'UNION',
        'UNIQUE', 'UPDATE', 'USING', 'VACUUM', 'VALUES', 'VIEW', 'VIRTUAL', 'WHEN', 'WHERE' ];
    
    var indentUnit = config.indentUnit;
    var bracket = null;
    function tokenBase(stream, state) {
        bracket = null;
        c = stream.next();
        switch(c){
        case ' ': case '\t': case '\n': case '\f': case '\r':
            stream.eat(c);
            return null;
        case '-':
            if(stream.eat('-')){
                stream.skipToEnd();
                return "comment";
            }
            return 'operator';
        case '(': case ')':
            bracket = c;
            return 'bracket';
        case ';':
            return null;
        case '+': case '*':
            return 'operator';
        case '/':
            if(stream.peek()=='*'){
                stream.next();
                state.tokenize = tokenComment;
                return state.tokenize(stream, state);
            }
            return 'operator';
        case '%':
            return 'operator';
        case '=':
            stream.match(/==|=/);
            return 'operator';
        case '<':
            stream.match(/<=|<>|<<|</);
            return 'operator';
        case '>':
            stream.match(/>=|>>|>/);
            return 'operator';
        case '!':
            stream.match(/\|\||\|/);
            return 'operator';
        case ',':
            return null;
        case '&': case '~':
            return 'operator';
        case '[':
            state.tokenize = tokenOpLiteral(']');
            return state.tokenize(stream, state);
        case '`': case '"':
            state.tokenize = tokenOpLiteral(c);
            return state.tokenize(stream, state);
        case '\'':
            state.tokenize = tokenLiteral(c);
            return state.tokenize(stream, state);
        case '.':
            if(stream.match(/\d+([eE][+-]?\d+)?/)){
                return 'number';
            }else{
                return 'operator';
            }
        case '0': case '1': case '2': case '3': case '4':
        case '5': case '6': case '7': case '8': case '9':
            if(stream.match(/^\d*(\.\d*)?([eE][+-]?\d+)?/)){
                return 'number';
            }else{
                stream.match(/[0-9a-zA-Z_$\x7F-\xFF]+/);
                return 'error';
            }
        case '?':
        case '#':
            return (stream.match(/\d+/, true) ? 'variable': 'error');
        case '$': case '@': case ':':
            return (state.match(/^[0-9a-zA-Z_$\x7F-\xFF]+/, true) ? 'variable' : 'error');
        case 'x': case 'X':
            if(stream.eat('\'')){
                state.tokenize = tokenLiteral('\'');
                return state.tokenize(stream, state);
            }
        default:
            if(/[0-9a-zA-Z_$\x7F-\xFF]/.test(c)){
                stream.eatWhile(/[0-9a-zA-Z_$\x7F-\xFF]/);
                word = stream.current().toUpperCase();
                if(word=='BEGIN'){
                    bracket = '(';
                }else if(word=='END'){
                    bracket = ')';
                }
                return ((keywords.indexOf(word)==-1) ? 'variable' : 'keyword');
            }else{
                return 'error';
            }
        }
        
        return 'error';
    }
    function tokenLiteral(quote) {
        return function(stream, state) {
            var escaped = false, ch;
            while ((ch = stream.next()) != null) {
                if (ch == quote && !escaped) {
                    state.tokenize = tokenBase;
                    break;
                }
                escaped = !escaped && ch == quote;
            }
            return "string";
        };
    }

    function tokenOpLiteral(quote) {
        return function(stream, state) {
            var escaped = false, ch;
            while ((ch = stream.next()) != null) {
                if (ch == quote && !escaped) {
                    state.tokenize = tokenBase;
                    break;
                }
                escaped = !escaped && ch == quote;
            }
            return "variable-2";
        };
    }
    function tokenComment(stream, state) {
        for (;;) {
            if (stream.skipTo("*")) {
                stream.next();
                if (stream.eat("/")) {
                    state.tokenize = tokenBase;
                    break;
                }
            } else {
                stream.skipToEnd();
                break;
            }
        }
        return "comment";
    }
    
    function pushContext(state, type, col) {
        state.context = {prev: state.context, indent: state.indent, col: col, type: type};
    }
    
    function popContext(state) {
        if(state.context) state.context = state.context.prev;
    }
    
    return {
        token: function(stream, state) {
            if (stream.sol()) {
                state.indent = stream.indentation();
            }
            if (stream.eatSpace()) return null;
            var style = state.tokenize(stream, state);
            if (bracket == "(") {
                pushContext(state, ")", stream.column());
            }else if (bracket==")") {
                popContext(state);
            }
            return style;
        },
        startState: function() {
            return {
                tokenize: tokenBase,
                context: null,
                indent: 0,
                col: 0
            };
        },
        electricChars: '()Dd',
        indent: function(state, textAfter) {
            var closing = state.context && textAfter && (/^\)|^END$|^END\s+/i.test(textAfter));
            return (state.context==null ? 0 : state.context.indent + (closing ? 0 : indentUnit));
        }
    }
});
