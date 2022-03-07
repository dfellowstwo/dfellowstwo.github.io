(function (root, factory) {
  factory();
  root.aeris.VERSION = '1.0.2';
}(this, function () {/**
 * @license almond 0.2.9 Copyright (c) 2011-2014, The Dojo Foundation All Rights Reserved.
 * Available via the MIT or new BSD license.
 * see: http://github.com/jrburke/almond for details
 */
//Going sloppy to avoid 'use strict' string cost, but strict practices should
//be followed.
/*jslint sloppy: true */
/*global setTimeout: false */

var requirejs, require, define;
(function (undef) {
    var main, req, makeMap, handlers,
        defined = {},
        waiting = {},
        config = {},
        defining = {},
        hasOwn = Object.prototype.hasOwnProperty,
        aps = [].slice,
        jsSuffixRegExp = /\.js$/;

    function hasProp(obj, prop) {
        return hasOwn.call(obj, prop);
    }

    /**
     * Given a relative module name, like ./something, normalize it to
     * a real name that can be mapped to a path.
     * @param {String} name the relative name
     * @param {String} baseName a real name that the name arg is relative
     * to.
     * @returns {String} normalized name
     */
    function normalize(name, baseName) {
        var nameParts, nameSegment, mapValue, foundMap, lastIndex,
            foundI, foundStarMap, starI, i, j, part,
            baseParts = baseName && baseName.split("/"),
            map = config.map,
            starMap = (map && map['*']) || {};

        //Adjust any relative paths.
        if (name && name.charAt(0) === ".") {
            //If have a base name, try to normalize against it,
            //otherwise, assume it is a top-level require that will
            //be relative to baseUrl in the end.
            if (baseName) {
                //Convert baseName to array, and lop off the last part,
                //so that . matches that "directory" and not name of the baseName's
                //module. For instance, baseName of "one/two/three", maps to
                //"one/two/three.js", but we want the directory, "one/two" for
                //this normalization.
                baseParts = baseParts.slice(0, baseParts.length - 1);
                name = name.split('/');
                lastIndex = name.length - 1;

                // Node .js allowance:
                if (config.nodeIdCompat && jsSuffixRegExp.test(name[lastIndex])) {
                    name[lastIndex] = name[lastIndex].replace(jsSuffixRegExp, '');
                }

                name = baseParts.concat(name);

                //start trimDots
                for (i = 0; i < name.length; i += 1) {
                    part = name[i];
                    if (part === ".") {
                        name.splice(i, 1);
                        i -= 1;
                    } else if (part === "..") {
                        if (i === 1 && (name[2] === '..' || name[0] === '..')) {
                            //End of the line. Keep at least one non-dot
                            //path segment at the front so it can be mapped
                            //correctly to disk. Otherwise, there is likely
                            //no path mapping for a path starting with '..'.
                            //This can still fail, but catches the most reasonable
                            //uses of ..
                            break;
                        } else if (i > 0) {
                            name.splice(i - 1, 2);
                            i -= 2;
                        }
                    }
                }
                //end trimDots

                name = name.join("/");
            } else if (name.indexOf('./') === 0) {
                // No baseName, so this is ID is resolved relative
                // to baseUrl, pull off the leading dot.
                name = name.substring(2);
            }
        }

        //Apply map config if available.
        if ((baseParts || starMap) && map) {
            nameParts = name.split('/');

            for (i = nameParts.length; i > 0; i -= 1) {
                nameSegment = nameParts.slice(0, i).join("/");

                if (baseParts) {
                    //Find the longest baseName segment match in the config.
                    //So, do joins on the biggest to smallest lengths of baseParts.
                    for (j = baseParts.length; j > 0; j -= 1) {
                        mapValue = map[baseParts.slice(0, j).join('/')];

                        //baseName segment has  config, find if it has one for
                        //this name.
                        if (mapValue) {
                            mapValue = mapValue[nameSegment];
                            if (mapValue) {
                                //Match, update name to the new value.
                                foundMap = mapValue;
                                foundI = i;
                                break;
                            }
                        }
                    }
                }

                if (foundMap) {
                    break;
                }

                //Check for a star map match, but just hold on to it,
                //if there is a shorter segment match later in a matching
                //config, then favor over this star map.
                if (!foundStarMap && starMap && starMap[nameSegment]) {
                    foundStarMap = starMap[nameSegment];
                    starI = i;
                }
            }

            if (!foundMap && foundStarMap) {
                foundMap = foundStarMap;
                foundI = starI;
            }

            if (foundMap) {
                nameParts.splice(0, foundI, foundMap);
                name = nameParts.join('/');
            }
        }

        return name;
    }

    function makeRequire(relName, forceSync) {
        return function () {
            //A version of a require function that passes a moduleName
            //value for items that may need to
            //look up paths relative to the moduleName
            return req.apply(undef, aps.call(arguments, 0).concat([relName, forceSync]));
        };
    }

    function makeNormalize(relName) {
        return function (name) {
            return normalize(name, relName);
        };
    }

    function makeLoad(depName) {
        return function (value) {
            defined[depName] = value;
        };
    }

    function callDep(name) {
        if (hasProp(waiting, name)) {
            var args = waiting[name];
            delete waiting[name];
            defining[name] = true;
            main.apply(undef, args);
        }

        if (!hasProp(defined, name) && !hasProp(defining, name)) {
            throw new Error('No ' + name);
        }
        return defined[name];
    }

    //Turns a plugin!resource to [plugin, resource]
    //with the plugin being undefined if the name
    //did not have a plugin prefix.
    function splitPrefix(name) {
        var prefix,
            index = name ? name.indexOf('!') : -1;
        if (index > -1) {
            prefix = name.substring(0, index);
            name = name.substring(index + 1, name.length);
        }
        return [prefix, name];
    }

    /**
     * Makes a name map, normalizing the name, and using a plugin
     * for normalization if necessary. Grabs a ref to plugin
     * too, as an optimization.
     */
    makeMap = function (name, relName) {
        var plugin,
            parts = splitPrefix(name),
            prefix = parts[0];

        name = parts[1];

        if (prefix) {
            prefix = normalize(prefix, relName);
            plugin = callDep(prefix);
        }

        //Normalize according
        if (prefix) {
            if (plugin && plugin.normalize) {
                name = plugin.normalize(name, makeNormalize(relName));
            } else {
                name = normalize(name, relName);
            }
        } else {
            name = normalize(name, relName);
            parts = splitPrefix(name);
            prefix = parts[0];
            name = parts[1];
            if (prefix) {
                plugin = callDep(prefix);
            }
        }

        //Using ridiculous property names for space reasons
        return {
            f: prefix ? prefix + '!' + name : name, //fullName
            n: name,
            pr: prefix,
            p: plugin
        };
    };

    function makeConfig(name) {
        return function () {
            return (config && config.config && config.config[name]) || {};
        };
    }

    handlers = {
        require: function (name) {
            return makeRequire(name);
        },
        exports: function (name) {
            var e = defined[name];
            if (typeof e !== 'undefined') {
                return e;
            } else {
                return (defined[name] = {});
            }
        },
        module: function (name) {
            return {
                id: name,
                uri: '',
                exports: defined[name],
                config: makeConfig(name)
            };
        }
    };

    main = function (name, deps, callback, relName) {
        var cjsModule, depName, ret, map, i,
            args = [],
            callbackType = typeof callback,
            usingExports;

        //Use name if no relName
        relName = relName || name;

        //Call the callback to define the module, if necessary.
        if (callbackType === 'undefined' || callbackType === 'function') {
            //Pull out the defined dependencies and pass the ordered
            //values to the callback.
            //Default to [require, exports, module] if no deps
            deps = !deps.length && callback.length ? ['require', 'exports', 'module'] : deps;
            for (i = 0; i < deps.length; i += 1) {
                map = makeMap(deps[i], relName);
                depName = map.f;

                //Fast path CommonJS standard dependencies.
                if (depName === "require") {
                    args[i] = handlers.require(name);
                } else if (depName === "exports") {
                    //CommonJS module spec 1.1
                    args[i] = handlers.exports(name);
                    usingExports = true;
                } else if (depName === "module") {
                    //CommonJS module spec 1.1
                    cjsModule = args[i] = handlers.module(name);
                } else if (hasProp(defined, depName) ||
                           hasProp(waiting, depName) ||
                           hasProp(defining, depName)) {
                    args[i] = callDep(depName);
                } else if (map.p) {
                    map.p.load(map.n, makeRequire(relName, true), makeLoad(depName), {});
                    args[i] = defined[depName];
                } else {
                    throw new Error(name + ' missing ' + depName);
                }
            }

            ret = callback ? callback.apply(defined[name], args) : undefined;

            if (name) {
                //If setting exports via "module" is in play,
                //favor that over return value and exports. After that,
                //favor a non-undefined return value over exports use.
                if (cjsModule && cjsModule.exports !== undef &&
                        cjsModule.exports !== defined[name]) {
                    defined[name] = cjsModule.exports;
                } else if (ret !== undef || !usingExports) {
                    //Use the return value from the function.
                    defined[name] = ret;
                }
            }
        } else if (name) {
            //May just be an object definition for the module. Only
            //worry about defining if have a module name.
            defined[name] = callback;
        }
    };

    requirejs = require = req = function (deps, callback, relName, forceSync, alt) {
        if (typeof deps === "string") {
            if (handlers[deps]) {
                //callback in this case is really relName
                return handlers[deps](callback);
            }
            //Just return the module wanted. In this scenario, the
            //deps arg is the module name, and second arg (if passed)
            //is just the relName.
            //Normalize module name, if it contains . or ..
            return callDep(makeMap(deps, callback).f);
        } else if (!deps.splice) {
            //deps is a config object, not an array.
            config = deps;
            if (config.deps) {
                req(config.deps, config.callback);
            }
            if (!callback) {
                return;
            }

            if (callback.splice) {
                //callback is an array, which means it is a dependency list.
                //Adjust args if there are dependencies
                deps = callback;
                callback = relName;
                relName = null;
            } else {
                deps = undef;
            }
        }

        //Support require(['a'])
        callback = callback || function () {};

        //If relName is a function, it is an errback handler,
        //so remove it.
        if (typeof relName === 'function') {
            relName = forceSync;
            forceSync = alt;
        }

        //Simulate async callback;
        if (forceSync) {
            main(undef, deps, callback, relName);
        } else {
            //Using a non-zero value because of concern for what old browsers
            //do, and latest browsers "upgrade" to 4 if lower value is used:
            //http://www.whatwg.org/specs/web-apps/current-work/multipage/timers.html#dom-windowtimers-settimeout:
            //If want a value immediately, use require('id') instead -- something
            //that works in almond on the global level, but not guaranteed and
            //unlikely to work in other AMD implementations.
            setTimeout(function () {
                main(undef, deps, callback, relName);
            }, 4);
        }

        return req;
    };

    /**
     * Just drops the config on the floor, but returns req in case
     * the config return value is used.
     */
    req.config = function (cfg) {
        return req(cfg);
    };

    /**
     * Expose module registry for debugging and tooling
     */
    requirejs._defined = defined;

    define = function (name, deps, callback) {

        //This module may not have dependencies
        if (!deps.splice) {
            //deps is not an array, so probably means
            //an object literal or factory function for
            //the value. Adjust args.
            callback = deps;
            deps = [];
        }

        if (!hasProp(defined, name) && !hasProp(waiting, name)) {
            waiting[name] = [name, deps, callback];
        }
    };

    define.amd = {
        jQuery: true
    };
}());

define("bower_components/almond/almond", function(){});

//     Underscore.js 1.6.0
//     http://underscorejs.org
//     (c) 2009-2014 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Underscore may be freely distributed under the MIT license.

(function() {

  // Baseline setup
  // --------------

  // Establish the root object, `window` in the browser, or `exports` on the server.
  var root = this;

  // Save the previous value of the `_` variable.
  var previousUnderscore = root._;

  // Establish the object that gets returned to break out of a loop iteration.
  var breaker = {};

  // Save bytes in the minified (but not gzipped) version:
  var ArrayProto = Array.prototype, ObjProto = Object.prototype, FuncProto = Function.prototype;

  // Create quick reference variables for speed access to core prototypes.
  var
    push             = ArrayProto.push,
    slice            = ArrayProto.slice,
    concat           = ArrayProto.concat,
    toString         = ObjProto.toString,
    hasOwnProperty   = ObjProto.hasOwnProperty;

  // All **ECMAScript 5** native function implementations that we hope to use
  // are declared here.
  var
    nativeForEach      = ArrayProto.forEach,
    nativeMap          = ArrayProto.map,
    nativeReduce       = ArrayProto.reduce,
    nativeReduceRight  = ArrayProto.reduceRight,
    nativeFilter       = ArrayProto.filter,
    nativeEvery        = ArrayProto.every,
    nativeSome         = ArrayProto.some,
    nativeIndexOf      = ArrayProto.indexOf,
    nativeLastIndexOf  = ArrayProto.lastIndexOf,
    nativeIsArray      = Array.isArray,
    nativeKeys         = Object.keys,
    nativeBind         = FuncProto.bind;

  // Create a safe reference to the Underscore object for use below.
  var _ = function(obj) {
    if (obj instanceof _) return obj;
    if (!(this instanceof _)) return new _(obj);
    this._wrapped = obj;
  };

  // Export the Underscore object for **Node.js**, with
  // backwards-compatibility for the old `require()` API. If we're in
  // the browser, add `_` as a global object via a string identifier,
  // for Closure Compiler "advanced" mode.
  if (typeof exports !== 'undefined') {
    if (typeof module !== 'undefined' && module.exports) {
      exports = module.exports = _;
    }
    exports._ = _;
  } else {
    root._ = _;
  }

  // Current version.
  _.VERSION = '1.6.0';

  // Collection Functions
  // --------------------

  // The cornerstone, an `each` implementation, aka `forEach`.
  // Handles objects with the built-in `forEach`, arrays, and raw objects.
  // Delegates to **ECMAScript 5**'s native `forEach` if available.
  var each = _.each = _.forEach = function(obj, iterator, context) {
    if (obj == null) return obj;
    if (nativeForEach && obj.forEach === nativeForEach) {
      obj.forEach(iterator, context);
    } else if (obj.length === +obj.length) {
      for (var i = 0, length = obj.length; i < length; i++) {
        if (iterator.call(context, obj[i], i, obj) === breaker) return;
      }
    } else {
      var keys = _.keys(obj);
      for (var i = 0, length = keys.length; i < length; i++) {
        if (iterator.call(context, obj[keys[i]], keys[i], obj) === breaker) return;
      }
    }
    return obj;
  };

  // Return the results of applying the iterator to each element.
  // Delegates to **ECMAScript 5**'s native `map` if available.
  _.map = _.collect = function(obj, iterator, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeMap && obj.map === nativeMap) return obj.map(iterator, context);
    each(obj, function(value, index, list) {
      results.push(iterator.call(context, value, index, list));
    });
    return results;
  };

  var reduceError = 'Reduce of empty array with no initial value';

  // **Reduce** builds up a single result from a list of values, aka `inject`,
  // or `foldl`. Delegates to **ECMAScript 5**'s native `reduce` if available.
  _.reduce = _.foldl = _.inject = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduce && obj.reduce === nativeReduce) {
      if (context) iterator = _.bind(iterator, context);
      return initial ? obj.reduce(iterator, memo) : obj.reduce(iterator);
    }
    each(obj, function(value, index, list) {
      if (!initial) {
        memo = value;
        initial = true;
      } else {
        memo = iterator.call(context, memo, value, index, list);
      }
    });
    if (!initial) throw new TypeError(reduceError);
    return memo;
  };

  // The right-associative version of reduce, also known as `foldr`.
  // Delegates to **ECMAScript 5**'s native `reduceRight` if available.
  _.reduceRight = _.foldr = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduceRight && obj.reduceRight === nativeReduceRight) {
      if (context) iterator = _.bind(iterator, context);
      return initial ? obj.reduceRight(iterator, memo) : obj.reduceRight(iterator);
    }
    var length = obj.length;
    if (length !== +length) {
      var keys = _.keys(obj);
      length = keys.length;
    }
    each(obj, function(value, index, list) {
      index = keys ? keys[--length] : --length;
      if (!initial) {
        memo = obj[index];
        initial = true;
      } else {
        memo = iterator.call(context, memo, obj[index], index, list);
      }
    });
    if (!initial) throw new TypeError(reduceError);
    return memo;
  };

  // Return the first value which passes a truth test. Aliased as `detect`.
  _.find = _.detect = function(obj, predicate, context) {
    var result;
    any(obj, function(value, index, list) {
      if (predicate.call(context, value, index, list)) {
        result = value;
        return true;
      }
    });
    return result;
  };

  // Return all the elements that pass a truth test.
  // Delegates to **ECMAScript 5**'s native `filter` if available.
  // Aliased as `select`.
  _.filter = _.select = function(obj, predicate, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeFilter && obj.filter === nativeFilter) return obj.filter(predicate, context);
    each(obj, function(value, index, list) {
      if (predicate.call(context, value, index, list)) results.push(value);
    });
    return results;
  };

  // Return all the elements for which a truth test fails.
  _.reject = function(obj, predicate, context) {
    return _.filter(obj, function(value, index, list) {
      return !predicate.call(context, value, index, list);
    }, context);
  };

  // Determine whether all of the elements match a truth test.
  // Delegates to **ECMAScript 5**'s native `every` if available.
  // Aliased as `all`.
  _.every = _.all = function(obj, predicate, context) {
    predicate || (predicate = _.identity);
    var result = true;
    if (obj == null) return result;
    if (nativeEvery && obj.every === nativeEvery) return obj.every(predicate, context);
    each(obj, function(value, index, list) {
      if (!(result = result && predicate.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if at least one element in the object matches a truth test.
  // Delegates to **ECMAScript 5**'s native `some` if available.
  // Aliased as `any`.
  var any = _.some = _.any = function(obj, predicate, context) {
    predicate || (predicate = _.identity);
    var result = false;
    if (obj == null) return result;
    if (nativeSome && obj.some === nativeSome) return obj.some(predicate, context);
    each(obj, function(value, index, list) {
      if (result || (result = predicate.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if the array or object contains a given value (using `===`).
  // Aliased as `include`.
  _.contains = _.include = function(obj, target) {
    if (obj == null) return false;
    if (nativeIndexOf && obj.indexOf === nativeIndexOf) return obj.indexOf(target) != -1;
    return any(obj, function(value) {
      return value === target;
    });
  };

  // Invoke a method (with arguments) on every item in a collection.
  _.invoke = function(obj, method) {
    var args = slice.call(arguments, 2);
    var isFunc = _.isFunction(method);
    return _.map(obj, function(value) {
      return (isFunc ? method : value[method]).apply(value, args);
    });
  };

  // Convenience version of a common use case of `map`: fetching a property.
  _.pluck = function(obj, key) {
    return _.map(obj, _.property(key));
  };

  // Convenience version of a common use case of `filter`: selecting only objects
  // containing specific `key:value` pairs.
  _.where = function(obj, attrs) {
    return _.filter(obj, _.matches(attrs));
  };

  // Convenience version of a common use case of `find`: getting the first object
  // containing specific `key:value` pairs.
  _.findWhere = function(obj, attrs) {
    return _.find(obj, _.matches(attrs));
  };

  // Return the maximum element or (element-based computation).
  // Can't optimize arrays of integers longer than 65,535 elements.
  // See [WebKit Bug 80797](https://bugs.webkit.org/show_bug.cgi?id=80797)
  _.max = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.max.apply(Math, obj);
    }
    var result = -Infinity, lastComputed = -Infinity;
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      if (computed > lastComputed) {
        result = value;
        lastComputed = computed;
      }
    });
    return result;
  };

  // Return the minimum element (or element-based computation).
  _.min = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.min.apply(Math, obj);
    }
    var result = Infinity, lastComputed = Infinity;
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      if (computed < lastComputed) {
        result = value;
        lastComputed = computed;
      }
    });
    return result;
  };

  // Shuffle an array, using the modern version of the
  // [Fisher-Yates shuffle](http://en.wikipedia.org/wiki/Fisher–Yates_shuffle).
  _.shuffle = function(obj) {
    var rand;
    var index = 0;
    var shuffled = [];
    each(obj, function(value) {
      rand = _.random(index++);
      shuffled[index - 1] = shuffled[rand];
      shuffled[rand] = value;
    });
    return shuffled;
  };

  // Sample **n** random values from a collection.
  // If **n** is not specified, returns a single random element.
  // The internal `guard` argument allows it to work with `map`.
  _.sample = function(obj, n, guard) {
    if (n == null || guard) {
      if (obj.length !== +obj.length) obj = _.values(obj);
      return obj[_.random(obj.length - 1)];
    }
    return _.shuffle(obj).slice(0, Math.max(0, n));
  };

  // An internal function to generate lookup iterators.
  var lookupIterator = function(value) {
    if (value == null) return _.identity;
    if (_.isFunction(value)) return value;
    return _.property(value);
  };

  // Sort the object's values by a criterion produced by an iterator.
  _.sortBy = function(obj, iterator, context) {
    iterator = lookupIterator(iterator);
    return _.pluck(_.map(obj, function(value, index, list) {
      return {
        value: value,
        index: index,
        criteria: iterator.call(context, value, index, list)
      };
    }).sort(function(left, right) {
      var a = left.criteria;
      var b = right.criteria;
      if (a !== b) {
        if (a > b || a === void 0) return 1;
        if (a < b || b === void 0) return -1;
      }
      return left.index - right.index;
    }), 'value');
  };

  // An internal function used for aggregate "group by" operations.
  var group = function(behavior) {
    return function(obj, iterator, context) {
      var result = {};
      iterator = lookupIterator(iterator);
      each(obj, function(value, index) {
        var key = iterator.call(context, value, index, obj);
        behavior(result, key, value);
      });
      return result;
    };
  };

  // Groups the object's values by a criterion. Pass either a string attribute
  // to group by, or a function that returns the criterion.
  _.groupBy = group(function(result, key, value) {
    _.has(result, key) ? result[key].push(value) : result[key] = [value];
  });

  // Indexes the object's values by a criterion, similar to `groupBy`, but for
  // when you know that your index values will be unique.
  _.indexBy = group(function(result, key, value) {
    result[key] = value;
  });

  // Counts instances of an object that group by a certain criterion. Pass
  // either a string attribute to count by, or a function that returns the
  // criterion.
  _.countBy = group(function(result, key) {
    _.has(result, key) ? result[key]++ : result[key] = 1;
  });

  // Use a comparator function to figure out the smallest index at which
  // an object should be inserted so as to maintain order. Uses binary search.
  _.sortedIndex = function(array, obj, iterator, context) {
    iterator = lookupIterator(iterator);
    var value = iterator.call(context, obj);
    var low = 0, high = array.length;
    while (low < high) {
      var mid = (low + high) >>> 1;
      iterator.call(context, array[mid]) < value ? low = mid + 1 : high = mid;
    }
    return low;
  };

  // Safely create a real, live array from anything iterable.
  _.toArray = function(obj) {
    if (!obj) return [];
    if (_.isArray(obj)) return slice.call(obj);
    if (obj.length === +obj.length) return _.map(obj, _.identity);
    return _.values(obj);
  };

  // Return the number of elements in an object.
  _.size = function(obj) {
    if (obj == null) return 0;
    return (obj.length === +obj.length) ? obj.length : _.keys(obj).length;
  };

  // Array Functions
  // ---------------

  // Get the first element of an array. Passing **n** will return the first N
  // values in the array. Aliased as `head` and `take`. The **guard** check
  // allows it to work with `_.map`.
  _.first = _.head = _.take = function(array, n, guard) {
    if (array == null) return void 0;
    if ((n == null) || guard) return array[0];
    if (n < 0) return [];
    return slice.call(array, 0, n);
  };

  // Returns everything but the last entry of the array. Especially useful on
  // the arguments object. Passing **n** will return all the values in
  // the array, excluding the last N. The **guard** check allows it to work with
  // `_.map`.
  _.initial = function(array, n, guard) {
    return slice.call(array, 0, array.length - ((n == null) || guard ? 1 : n));
  };

  // Get the last element of an array. Passing **n** will return the last N
  // values in the array. The **guard** check allows it to work with `_.map`.
  _.last = function(array, n, guard) {
    if (array == null) return void 0;
    if ((n == null) || guard) return array[array.length - 1];
    return slice.call(array, Math.max(array.length - n, 0));
  };

  // Returns everything but the first entry of the array. Aliased as `tail` and `drop`.
  // Especially useful on the arguments object. Passing an **n** will return
  // the rest N values in the array. The **guard**
  // check allows it to work with `_.map`.
  _.rest = _.tail = _.drop = function(array, n, guard) {
    return slice.call(array, (n == null) || guard ? 1 : n);
  };

  // Trim out all falsy values from an array.
  _.compact = function(array) {
    return _.filter(array, _.identity);
  };

  // Internal implementation of a recursive `flatten` function.
  var flatten = function(input, shallow, output) {
    if (shallow && _.every(input, _.isArray)) {
      return concat.apply(output, input);
    }
    each(input, function(value) {
      if (_.isArray(value) || _.isArguments(value)) {
        shallow ? push.apply(output, value) : flatten(value, shallow, output);
      } else {
        output.push(value);
      }
    });
    return output;
  };

  // Flatten out an array, either recursively (by default), or just one level.
  _.flatten = function(array, shallow) {
    return flatten(array, shallow, []);
  };

  // Return a version of the array that does not contain the specified value(s).
  _.without = function(array) {
    return _.difference(array, slice.call(arguments, 1));
  };

  // Split an array into two arrays: one whose elements all satisfy the given
  // predicate, and one whose elements all do not satisfy the predicate.
  _.partition = function(array, predicate) {
    var pass = [], fail = [];
    each(array, function(elem) {
      (predicate(elem) ? pass : fail).push(elem);
    });
    return [pass, fail];
  };

  // Produce a duplicate-free version of the array. If the array has already
  // been sorted, you have the option of using a faster algorithm.
  // Aliased as `unique`.
  _.uniq = _.unique = function(array, isSorted, iterator, context) {
    if (_.isFunction(isSorted)) {
      context = iterator;
      iterator = isSorted;
      isSorted = false;
    }
    var initial = iterator ? _.map(array, iterator, context) : array;
    var results = [];
    var seen = [];
    each(initial, function(value, index) {
      if (isSorted ? (!index || seen[seen.length - 1] !== value) : !_.contains(seen, value)) {
        seen.push(value);
        results.push(array[index]);
      }
    });
    return results;
  };

  // Produce an array that contains the union: each distinct element from all of
  // the passed-in arrays.
  _.union = function() {
    return _.uniq(_.flatten(arguments, true));
  };

  // Produce an array that contains every item shared between all the
  // passed-in arrays.
  _.intersection = function(array) {
    var rest = slice.call(arguments, 1);
    return _.filter(_.uniq(array), function(item) {
      return _.every(rest, function(other) {
        return _.contains(other, item);
      });
    });
  };

  // Take the difference between one array and a number of other arrays.
  // Only the elements present in just the first array will remain.
  _.difference = function(array) {
    var rest = concat.apply(ArrayProto, slice.call(arguments, 1));
    return _.filter(array, function(value){ return !_.contains(rest, value); });
  };

  // Zip together multiple lists into a single array -- elements that share
  // an index go together.
  _.zip = function() {
    var length = _.max(_.pluck(arguments, 'length').concat(0));
    var results = new Array(length);
    for (var i = 0; i < length; i++) {
      results[i] = _.pluck(arguments, '' + i);
    }
    return results;
  };

  // Converts lists into objects. Pass either a single array of `[key, value]`
  // pairs, or two parallel arrays of the same length -- one of keys, and one of
  // the corresponding values.
  _.object = function(list, values) {
    if (list == null) return {};
    var result = {};
    for (var i = 0, length = list.length; i < length; i++) {
      if (values) {
        result[list[i]] = values[i];
      } else {
        result[list[i][0]] = list[i][1];
      }
    }
    return result;
  };

  // If the browser doesn't supply us with indexOf (I'm looking at you, **MSIE**),
  // we need this function. Return the position of the first occurrence of an
  // item in an array, or -1 if the item is not included in the array.
  // Delegates to **ECMAScript 5**'s native `indexOf` if available.
  // If the array is large and already in sort order, pass `true`
  // for **isSorted** to use binary search.
  _.indexOf = function(array, item, isSorted) {
    if (array == null) return -1;
    var i = 0, length = array.length;
    if (isSorted) {
      if (typeof isSorted == 'number') {
        i = (isSorted < 0 ? Math.max(0, length + isSorted) : isSorted);
      } else {
        i = _.sortedIndex(array, item);
        return array[i] === item ? i : -1;
      }
    }
    if (nativeIndexOf && array.indexOf === nativeIndexOf) return array.indexOf(item, isSorted);
    for (; i < length; i++) if (array[i] === item) return i;
    return -1;
  };

  // Delegates to **ECMAScript 5**'s native `lastIndexOf` if available.
  _.lastIndexOf = function(array, item, from) {
    if (array == null) return -1;
    var hasIndex = from != null;
    if (nativeLastIndexOf && array.lastIndexOf === nativeLastIndexOf) {
      return hasIndex ? array.lastIndexOf(item, from) : array.lastIndexOf(item);
    }
    var i = (hasIndex ? from : array.length);
    while (i--) if (array[i] === item) return i;
    return -1;
  };

  // Generate an integer Array containing an arithmetic progression. A port of
  // the native Python `range()` function. See
  // [the Python documentation](http://docs.python.org/library/functions.html#range).
  _.range = function(start, stop, step) {
    if (arguments.length <= 1) {
      stop = start || 0;
      start = 0;
    }
    step = arguments[2] || 1;

    var length = Math.max(Math.ceil((stop - start) / step), 0);
    var idx = 0;
    var range = new Array(length);

    while(idx < length) {
      range[idx++] = start;
      start += step;
    }

    return range;
  };

  // Function (ahem) Functions
  // ------------------

  // Reusable constructor function for prototype setting.
  var ctor = function(){};

  // Create a function bound to a given object (assigning `this`, and arguments,
  // optionally). Delegates to **ECMAScript 5**'s native `Function.bind` if
  // available.
  _.bind = function(func, context) {
    var args, bound;
    if (nativeBind && func.bind === nativeBind) return nativeBind.apply(func, slice.call(arguments, 1));
    if (!_.isFunction(func)) throw new TypeError;
    args = slice.call(arguments, 2);
    return bound = function() {
      if (!(this instanceof bound)) return func.apply(context, args.concat(slice.call(arguments)));
      ctor.prototype = func.prototype;
      var self = new ctor;
      ctor.prototype = null;
      var result = func.apply(self, args.concat(slice.call(arguments)));
      if (Object(result) === result) return result;
      return self;
    };
  };

  // Partially apply a function by creating a version that has had some of its
  // arguments pre-filled, without changing its dynamic `this` context. _ acts
  // as a placeholder, allowing any combination of arguments to be pre-filled.
  _.partial = function(func) {
    var boundArgs = slice.call(arguments, 1);
    return function() {
      var position = 0;
      var args = boundArgs.slice();
      for (var i = 0, length = args.length; i < length; i++) {
        if (args[i] === _) args[i] = arguments[position++];
      }
      while (position < arguments.length) args.push(arguments[position++]);
      return func.apply(this, args);
    };
  };

  // Bind a number of an object's methods to that object. Remaining arguments
  // are the method names to be bound. Useful for ensuring that all callbacks
  // defined on an object belong to it.
  _.bindAll = function(obj) {
    var funcs = slice.call(arguments, 1);
    if (funcs.length === 0) throw new Error('bindAll must be passed function names');
    each(funcs, function(f) { obj[f] = _.bind(obj[f], obj); });
    return obj;
  };

  // Memoize an expensive function by storing its results.
  _.memoize = function(func, hasher) {
    var memo = {};
    hasher || (hasher = _.identity);
    return function() {
      var key = hasher.apply(this, arguments);
      return _.has(memo, key) ? memo[key] : (memo[key] = func.apply(this, arguments));
    };
  };

  // Delays a function for the given number of milliseconds, and then calls
  // it with the arguments supplied.
  _.delay = function(func, wait) {
    var args = slice.call(arguments, 2);
    return setTimeout(function(){ return func.apply(null, args); }, wait);
  };

  // Defers a function, scheduling it to run after the current call stack has
  // cleared.
  _.defer = function(func) {
    return _.delay.apply(_, [func, 1].concat(slice.call(arguments, 1)));
  };

  // Returns a function, that, when invoked, will only be triggered at most once
  // during a given window of time. Normally, the throttled function will run
  // as much as it can, without ever going more than once per `wait` duration;
  // but if you'd like to disable the execution on the leading edge, pass
  // `{leading: false}`. To disable execution on the trailing edge, ditto.
  _.throttle = function(func, wait, options) {
    var context, args, result;
    var timeout = null;
    var previous = 0;
    options || (options = {});
    var later = function() {
      previous = options.leading === false ? 0 : _.now();
      timeout = null;
      result = func.apply(context, args);
      context = args = null;
    };
    return function() {
      var now = _.now();
      if (!previous && options.leading === false) previous = now;
      var remaining = wait - (now - previous);
      context = this;
      args = arguments;
      if (remaining <= 0) {
        clearTimeout(timeout);
        timeout = null;
        previous = now;
        result = func.apply(context, args);
        context = args = null;
      } else if (!timeout && options.trailing !== false) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };
  };

  // Returns a function, that, as long as it continues to be invoked, will not
  // be triggered. The function will be called after it stops being called for
  // N milliseconds. If `immediate` is passed, trigger the function on the
  // leading edge, instead of the trailing.
  _.debounce = function(func, wait, immediate) {
    var timeout, args, context, timestamp, result;

    var later = function() {
      var last = _.now() - timestamp;
      if (last < wait) {
        timeout = setTimeout(later, wait - last);
      } else {
        timeout = null;
        if (!immediate) {
          result = func.apply(context, args);
          context = args = null;
        }
      }
    };

    return function() {
      context = this;
      args = arguments;
      timestamp = _.now();
      var callNow = immediate && !timeout;
      if (!timeout) {
        timeout = setTimeout(later, wait);
      }
      if (callNow) {
        result = func.apply(context, args);
        context = args = null;
      }

      return result;
    };
  };

  // Returns a function that will be executed at most one time, no matter how
  // often you call it. Useful for lazy initialization.
  _.once = function(func) {
    var ran = false, memo;
    return function() {
      if (ran) return memo;
      ran = true;
      memo = func.apply(this, arguments);
      func = null;
      return memo;
    };
  };

  // Returns the first function passed as an argument to the second,
  // allowing you to adjust arguments, run code before and after, and
  // conditionally execute the original function.
  _.wrap = function(func, wrapper) {
    return _.partial(wrapper, func);
  };

  // Returns a function that is the composition of a list of functions, each
  // consuming the return value of the function that follows.
  _.compose = function() {
    var funcs = arguments;
    return function() {
      var args = arguments;
      for (var i = funcs.length - 1; i >= 0; i--) {
        args = [funcs[i].apply(this, args)];
      }
      return args[0];
    };
  };

  // Returns a function that will only be executed after being called N times.
  _.after = function(times, func) {
    return function() {
      if (--times < 1) {
        return func.apply(this, arguments);
      }
    };
  };

  // Object Functions
  // ----------------

  // Retrieve the names of an object's properties.
  // Delegates to **ECMAScript 5**'s native `Object.keys`
  _.keys = function(obj) {
    if (!_.isObject(obj)) return [];
    if (nativeKeys) return nativeKeys(obj);
    var keys = [];
    for (var key in obj) if (_.has(obj, key)) keys.push(key);
    return keys;
  };

  // Retrieve the values of an object's properties.
  _.values = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var values = new Array(length);
    for (var i = 0; i < length; i++) {
      values[i] = obj[keys[i]];
    }
    return values;
  };

  // Convert an object into a list of `[key, value]` pairs.
  _.pairs = function(obj) {
    var keys = _.keys(obj);
    var length = keys.length;
    var pairs = new Array(length);
    for (var i = 0; i < length; i++) {
      pairs[i] = [keys[i], obj[keys[i]]];
    }
    return pairs;
  };

  // Invert the keys and values of an object. The values must be serializable.
  _.invert = function(obj) {
    var result = {};
    var keys = _.keys(obj);
    for (var i = 0, length = keys.length; i < length; i++) {
      result[obj[keys[i]]] = keys[i];
    }
    return result;
  };

  // Return a sorted list of the function names available on the object.
  // Aliased as `methods`
  _.functions = _.methods = function(obj) {
    var names = [];
    for (var key in obj) {
      if (_.isFunction(obj[key])) names.push(key);
    }
    return names.sort();
  };

  // Extend a given object with all the properties in passed-in object(s).
  _.extend = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      if (source) {
        for (var prop in source) {
          obj[prop] = source[prop];
        }
      }
    });
    return obj;
  };

  // Return a copy of the object only containing the whitelisted properties.
  _.pick = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    each(keys, function(key) {
      if (key in obj) copy[key] = obj[key];
    });
    return copy;
  };

   // Return a copy of the object without the blacklisted properties.
  _.omit = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    for (var key in obj) {
      if (!_.contains(keys, key)) copy[key] = obj[key];
    }
    return copy;
  };

  // Fill in a given object with default properties.
  _.defaults = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      if (source) {
        for (var prop in source) {
          if (obj[prop] === void 0) obj[prop] = source[prop];
        }
      }
    });
    return obj;
  };

  // Create a (shallow-cloned) duplicate of an object.
  _.clone = function(obj) {
    if (!_.isObject(obj)) return obj;
    return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
  };

  // Invokes interceptor with the obj, and then returns obj.
  // The primary purpose of this method is to "tap into" a method chain, in
  // order to perform operations on intermediate results within the chain.
  _.tap = function(obj, interceptor) {
    interceptor(obj);
    return obj;
  };

  // Internal recursive comparison function for `isEqual`.
  var eq = function(a, b, aStack, bStack) {
    // Identical objects are equal. `0 === -0`, but they aren't identical.
    // See the [Harmony `egal` proposal](http://wiki.ecmascript.org/doku.php?id=harmony:egal).
    if (a === b) return a !== 0 || 1 / a == 1 / b;
    // A strict comparison is necessary because `null == undefined`.
    if (a == null || b == null) return a === b;
    // Unwrap any wrapped objects.
    if (a instanceof _) a = a._wrapped;
    if (b instanceof _) b = b._wrapped;
    // Compare `[[Class]]` names.
    var className = toString.call(a);
    if (className != toString.call(b)) return false;
    switch (className) {
      // Strings, numbers, dates, and booleans are compared by value.
      case '[object String]':
        // Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
        // equivalent to `new String("5")`.
        return a == String(b);
      case '[object Number]':
        // `NaN`s are equivalent, but non-reflexive. An `egal` comparison is performed for
        // other numeric values.
        return a != +a ? b != +b : (a == 0 ? 1 / a == 1 / b : a == +b);
      case '[object Date]':
      case '[object Boolean]':
        // Coerce dates and booleans to numeric primitive values. Dates are compared by their
        // millisecond representations. Note that invalid dates with millisecond representations
        // of `NaN` are not equivalent.
        return +a == +b;
      // RegExps are compared by their source patterns and flags.
      case '[object RegExp]':
        return a.source == b.source &&
               a.global == b.global &&
               a.multiline == b.multiline &&
               a.ignoreCase == b.ignoreCase;
    }
    if (typeof a != 'object' || typeof b != 'object') return false;
    // Assume equality for cyclic structures. The algorithm for detecting cyclic
    // structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.
    var length = aStack.length;
    while (length--) {
      // Linear search. Performance is inversely proportional to the number of
      // unique nested structures.
      if (aStack[length] == a) return bStack[length] == b;
    }
    // Objects with different constructors are not equivalent, but `Object`s
    // from different frames are.
    var aCtor = a.constructor, bCtor = b.constructor;
    if (aCtor !== bCtor && !(_.isFunction(aCtor) && (aCtor instanceof aCtor) &&
                             _.isFunction(bCtor) && (bCtor instanceof bCtor))
                        && ('constructor' in a && 'constructor' in b)) {
      return false;
    }
    // Add the first object to the stack of traversed objects.
    aStack.push(a);
    bStack.push(b);
    var size = 0, result = true;
    // Recursively compare objects and arrays.
    if (className == '[object Array]') {
      // Compare array lengths to determine if a deep comparison is necessary.
      size = a.length;
      result = size == b.length;
      if (result) {
        // Deep compare the contents, ignoring non-numeric properties.
        while (size--) {
          if (!(result = eq(a[size], b[size], aStack, bStack))) break;
        }
      }
    } else {
      // Deep compare objects.
      for (var key in a) {
        if (_.has(a, key)) {
          // Count the expected number of properties.
          size++;
          // Deep compare each member.
          if (!(result = _.has(b, key) && eq(a[key], b[key], aStack, bStack))) break;
        }
      }
      // Ensure that both objects contain the same number of properties.
      if (result) {
        for (key in b) {
          if (_.has(b, key) && !(size--)) break;
        }
        result = !size;
      }
    }
    // Remove the first object from the stack of traversed objects.
    aStack.pop();
    bStack.pop();
    return result;
  };

  // Perform a deep comparison to check if two objects are equal.
  _.isEqual = function(a, b) {
    return eq(a, b, [], []);
  };

  // Is a given array, string, or object empty?
  // An "empty" object has no enumerable own-properties.
  _.isEmpty = function(obj) {
    if (obj == null) return true;
    if (_.isArray(obj) || _.isString(obj)) return obj.length === 0;
    for (var key in obj) if (_.has(obj, key)) return false;
    return true;
  };

  // Is a given value a DOM element?
  _.isElement = function(obj) {
    return !!(obj && obj.nodeType === 1);
  };

  // Is a given value an array?
  // Delegates to ECMA5's native Array.isArray
  _.isArray = nativeIsArray || function(obj) {
    return toString.call(obj) == '[object Array]';
  };

  // Is a given variable an object?
  _.isObject = function(obj) {
    return obj === Object(obj);
  };

  // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp.
  each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp'], function(name) {
    _['is' + name] = function(obj) {
      return toString.call(obj) == '[object ' + name + ']';
    };
  });

  // Define a fallback version of the method in browsers (ahem, IE), where
  // there isn't any inspectable "Arguments" type.
  if (!_.isArguments(arguments)) {
    _.isArguments = function(obj) {
      return !!(obj && _.has(obj, 'callee'));
    };
  }

  // Optimize `isFunction` if appropriate.
  if (typeof (/./) !== 'function') {
    _.isFunction = function(obj) {
      return typeof obj === 'function';
    };
  }

  // Is a given object a finite number?
  _.isFinite = function(obj) {
    return isFinite(obj) && !isNaN(parseFloat(obj));
  };

  // Is the given value `NaN`? (NaN is the only number which does not equal itself).
  _.isNaN = function(obj) {
    return _.isNumber(obj) && obj != +obj;
  };

  // Is a given value a boolean?
  _.isBoolean = function(obj) {
    return obj === true || obj === false || toString.call(obj) == '[object Boolean]';
  };

  // Is a given value equal to null?
  _.isNull = function(obj) {
    return obj === null;
  };

  // Is a given variable undefined?
  _.isUndefined = function(obj) {
    return obj === void 0;
  };

  // Shortcut function for checking if an object has a given property directly
  // on itself (in other words, not on a prototype).
  _.has = function(obj, key) {
    return hasOwnProperty.call(obj, key);
  };

  // Utility Functions
  // -----------------

  // Run Underscore.js in *noConflict* mode, returning the `_` variable to its
  // previous owner. Returns a reference to the Underscore object.
  _.noConflict = function() {
    root._ = previousUnderscore;
    return this;
  };

  // Keep the identity function around for default iterators.
  _.identity = function(value) {
    return value;
  };

  _.constant = function(value) {
    return function () {
      return value;
    };
  };

  _.property = function(key) {
    return function(obj) {
      return obj[key];
    };
  };

  // Returns a predicate for checking whether an object has a given set of `key:value` pairs.
  _.matches = function(attrs) {
    return function(obj) {
      if (obj === attrs) return true; //avoid comparing an object to itself.
      for (var key in attrs) {
        if (attrs[key] !== obj[key])
          return false;
      }
      return true;
    }
  };

  // Run a function **n** times.
  _.times = function(n, iterator, context) {
    var accum = Array(Math.max(0, n));
    for (var i = 0; i < n; i++) accum[i] = iterator.call(context, i);
    return accum;
  };

  // Return a random integer between min and max (inclusive).
  _.random = function(min, max) {
    if (max == null) {
      max = min;
      min = 0;
    }
    return min + Math.floor(Math.random() * (max - min + 1));
  };

  // A (possibly faster) way to get the current timestamp as an integer.
  _.now = Date.now || function() { return new Date().getTime(); };

  // List of HTML entities for escaping.
  var entityMap = {
    escape: {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#x27;'
    }
  };
  entityMap.unescape = _.invert(entityMap.escape);

  // Regexes containing the keys and values listed immediately above.
  var entityRegexes = {
    escape:   new RegExp('[' + _.keys(entityMap.escape).join('') + ']', 'g'),
    unescape: new RegExp('(' + _.keys(entityMap.unescape).join('|') + ')', 'g')
  };

  // Functions for escaping and unescaping strings to/from HTML interpolation.
  _.each(['escape', 'unescape'], function(method) {
    _[method] = function(string) {
      if (string == null) return '';
      return ('' + string).replace(entityRegexes[method], function(match) {
        return entityMap[method][match];
      });
    };
  });

  // If the value of the named `property` is a function then invoke it with the
  // `object` as context; otherwise, return it.
  _.result = function(object, property) {
    if (object == null) return void 0;
    var value = object[property];
    return _.isFunction(value) ? value.call(object) : value;
  };

  // Add your own custom functions to the Underscore object.
  _.mixin = function(obj) {
    each(_.functions(obj), function(name) {
      var func = _[name] = obj[name];
      _.prototype[name] = function() {
        var args = [this._wrapped];
        push.apply(args, arguments);
        return result.call(this, func.apply(_, args));
      };
    });
  };

  // Generate a unique integer id (unique within the entire client session).
  // Useful for temporary DOM ids.
  var idCounter = 0;
  _.uniqueId = function(prefix) {
    var id = ++idCounter + '';
    return prefix ? prefix + id : id;
  };

  // By default, Underscore uses ERB-style template delimiters, change the
  // following template settings to use alternative delimiters.
  _.templateSettings = {
    evaluate    : /<%([\s\S]+?)%>/g,
    interpolate : /<%=([\s\S]+?)%>/g,
    escape      : /<%-([\s\S]+?)%>/g
  };

  // When customizing `templateSettings`, if you don't want to define an
  // interpolation, evaluation or escaping regex, we need one that is
  // guaranteed not to match.
  var noMatch = /(.)^/;

  // Certain characters need to be escaped so that they can be put into a
  // string literal.
  var escapes = {
    "'":      "'",
    '\\':     '\\',
    '\r':     'r',
    '\n':     'n',
    '\t':     't',
    '\u2028': 'u2028',
    '\u2029': 'u2029'
  };

  var escaper = /\\|'|\r|\n|\t|\u2028|\u2029/g;

  // JavaScript micro-templating, similar to John Resig's implementation.
  // Underscore templating handles arbitrary delimiters, preserves whitespace,
  // and correctly escapes quotes within interpolated code.
  _.template = function(text, data, settings) {
    var render;
    settings = _.defaults({}, settings, _.templateSettings);

    // Combine delimiters into one regular expression via alternation.
    var matcher = new RegExp([
      (settings.escape || noMatch).source,
      (settings.interpolate || noMatch).source,
      (settings.evaluate || noMatch).source
    ].join('|') + '|$', 'g');

    // Compile the template source, escaping string literals appropriately.
    var index = 0;
    var source = "__p+='";
    text.replace(matcher, function(match, escape, interpolate, evaluate, offset) {
      source += text.slice(index, offset)
        .replace(escaper, function(match) { return '\\' + escapes[match]; });

      if (escape) {
        source += "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'";
      }
      if (interpolate) {
        source += "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'";
      }
      if (evaluate) {
        source += "';\n" + evaluate + "\n__p+='";
      }
      index = offset + match.length;
      return match;
    });
    source += "';\n";

    // If a variable is not specified, place data values in local scope.
    if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

    source = "var __t,__p='',__j=Array.prototype.join," +
      "print=function(){__p+=__j.call(arguments,'');};\n" +
      source + "return __p;\n";

    try {
      render = new Function(settings.variable || 'obj', '_', source);
    } catch (e) {
      e.source = source;
      throw e;
    }

    if (data) return render(data, _);
    var template = function(data) {
      return render.call(this, data, _);
    };

    // Provide the compiled function source as a convenience for precompilation.
    template.source = 'function(' + (settings.variable || 'obj') + '){\n' + source + '}';

    return template;
  };

  // Add a "chain" function, which will delegate to the wrapper.
  _.chain = function(obj) {
    return _(obj).chain();
  };

  // OOP
  // ---------------
  // If Underscore is called as a function, it returns a wrapped object that
  // can be used OO-style. This wrapper holds altered versions of all the
  // underscore functions. Wrapped objects may be chained.

  // Helper function to continue chaining intermediate results.
  var result = function(obj) {
    return this._chain ? _(obj).chain() : obj;
  };

  // Add all of the Underscore functions to the wrapper object.
  _.mixin(_);

  // Add all mutator Array functions to the wrapper.
  each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      var obj = this._wrapped;
      method.apply(obj, arguments);
      if ((name == 'shift' || name == 'splice') && obj.length === 0) delete obj[0];
      return result.call(this, obj);
    };
  });

  // Add all accessor Array functions to the wrapper.
  each(['concat', 'join', 'slice'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      return result.call(this, method.apply(this._wrapped, arguments));
    };
  });

  _.extend(_.prototype, {

    // Start chaining a wrapped Underscore object.
    chain: function() {
      this._chain = true;
      return this;
    },

    // Extracts the result from a wrapped and chained object.
    value: function() {
      return this._wrapped;
    }

  });

  // AMD registration happens at the end for compatibility with AMD loaders
  // that may not enforce next-turn semantics on modules. Even though general
  // practice for AMD registration is to be anonymous, underscore registers
  // as a named module because, like jQuery, it is a base library that is
  // popular enough to be bundled in a third party lib, but not be part of
  // an AMD load request. Those cases could generate an error when an
  // anonymous define() is called outside of a loader request.
  if (typeof define === 'function' && define.amd) {
    define('underscore', [], function() {
      return _;
    });
  }
}).call(this);

define('aeris/util',[
  'underscore'
], function(underscore) {
  // Check if we have a conflict
  var root = this;
  var _ = underscore.noConflict();

  // If the global underscore obj is undefined,
  // it means there was no previous owner,
  // and using noConflict() was unnecessary.
  // In this case, we want to
  // reassign underscore back to the global scope.
  // Other consumers may be expecting it in the
  // global scope, so we don't want to take it away from them.
  if (!root._) {
    root._ = _;
  }

  /**
   * Aeris library utilities.
   *
   * @class aeris.util
   * @static
   */
  var customUtil = {
    /**
     * Representation of an abstract method that needs overriding.
     * @method abstractMethod
     */
    abstractMethod: function() {
    },

    /**
     * Bind all methods in an object
     * to be run in the specified context.
     *
     * @param {Object} object
     * @param {Object=} opt_ctx Defaults to the object.
     */
    bindAllMethods: function(object, opt_ctx) {
      var ctx = opt_ctx || object;

      _.each(object, function(val, key) {
        if (_.isFunction(val)) {
          object[key] = val.bind(ctx);
        }
      });
    },


    /**
     * Inherit the prototype methods from one constructor into another.
     *
     * @param {Function} ChildCtor Child class.
     * @param {Function} ParentCtor Parent class.
     * @method inherits
     */
    inherits: function(ChildCtor, ParentCtor) {
      function TempCtor() {
        this.__Parent = ParentCtor;
      }

      TempCtor.prototype = ParentCtor.prototype;
      ChildCtor.prototype = new TempCtor();
      ChildCtor.prototype.constructor = ChildCtor;
    },


    /**
     * Expose a variable at the provided path under the
     * global namespace.
     *
     * Eg.
     *  _.expose(MyClass, 'aeris.someSubNs.MyClass');
     *  aeris.someSubNs.MyClass === MyClass     // true
     *
     * @param {*} obj The variable to expose.
     * @param {string} path Path that should be available.
     * @method expose
     */
    expose: function(obj, path) {
      var parts = path.split('.');
      var partsLength = parts.length;
      var ns = window;

      _.each(parts, function(part, i) {
        var isLastRef = i === (partsLength - 1);
        var nsValue = ns[part] || {};

        ns[part] = isLastRef ? obj : nsValue;

        // Move up our namespace pointer
        ns = ns[part];
      }, this);

      return obj;
    },


    /**
     * Ensures the defined path is available base on dot namespace.
     *
     * @param {string} path Path that should be available.
     * @return {undefined}
     * @method provide
     */
    provide: function(path) {
      return this.expose({}, path, false);
    },


    /**
     * Returns the average of an array
     * of numbers.
     *
     * @param {Array.<number>} arr
     * @method average
     */
    average: function(arr) {
      var numbers = _.isArray(arr) ? arr : util.argsToArray(arguments);
      var sum = numbers.reduce(function(sum, num) {
        return sum + num;
      }, 0);

      return sum / numbers.length;
    },


    /**
     * Similar to window.setInterval,
     * but allows for an optional context
     * and arguments to be passed to the function.
     *
     * @param {Function} fn
     * @param {number} wait
     * @param {Object} opt_ctx
     * @param {*} var_args Arguments to pass to the function.
     * @return {number} Reference to the interval, to be used with `clearInterval`.
     * @method interval
     */
    interval: function(fn, wait, opt_ctx, var_args) {
      var args = Array.prototype.slice.call(arguments, 3);
      if (opt_ctx) {
        fn = _.bind.apply(_, [fn, opt_ctx].concat(args));
      }

      return window.setInterval(fn, wait);
    },


    /**
     * Converts an arguments object to a true array.
     *
     * @param {Array} args object.
     * @return {Array}
     * @method argsToArray
     */
    argsToArray: function(args) {
      return Array.prototype.slice.call(args, 0);
    },


    /**
     * Return a reference to a object property
     * from a dot-notated string.
     *
     * For example
     *  var obj = { foo: bar: { baz: 'in here!' } } }
     *  _.path('foo.bar.baz')    // 'in here!'
     *
     * Returns undefined if no reference exists.
     *
     * eg:
     *  _.path('foo.boo.goo.moo.yoo')  // undefined
     *
     * This can be useful for attempting to access object
     * properties, when you're not sure if the entire object
     * is defined.
     *
     * @param {string} pathStr
     * @param {Object=} opt_scope
     *        The object within which to search for a reference.
     *        If no scope is defined, will search within the
     *        global scope (window).
     *
     * @return {*|undefined}
     * @method path
     */
    path: function(pathStr, opt_scope) {
      var parts, scope;

      if (!_.isString(pathStr) || !pathStr.length) {
        return undefined;
      }

      parts = pathStr.split('.');

      // Default to global scope
      scope = _.isUndefined(opt_scope) ? window : opt_scope;

      return _.reduce(parts, function(obj, i) {
        return _.isObject(obj) ? obj[i] : undefined;
      }, scope);
    },


    /**
     * A loose test for number-like objects.
     *
     * _.isNumeric(123)     // true
     * _.isNumeric('123')   // true
     * _.isNumeric('foo')   // false
     * _.isNumeric('10px')  // false
     * _.isNumberic('');    // false
     *
     * Thanks to this guy:
     * http://stackoverflow.com/a/1830844
     *
     * @param {*} obj
     * @return {Boolean}
     * @method isNumeric
     */
    isNumeric: function(obj) {
      return !_.isObject(obj) && !isNaN(parseFloat(obj)) && isFinite(obj);
    },


    /**
     * @param {Number} n
     * @return {Boolean}
     * @method isInteger
     */
    isInteger: function(n) {
      return _.isNumeric(n) && (n % 1 === 0);
    },


    isPlainObject: function(obj) {
      var isPlain = !_.isFunction(obj) && !_.isArray(obj);
      return _.isObject(obj) && isPlain;
    },


    /**
     * Throw an 'uncatchable' error.
     *
     * The error is 'uncatchable' because it is thrown
     * after the current call stack completes. This is generally
     * a bad idea, though it can be useful for forcing errors
     * to be thrown in promise callbacks.
     *
     * @param {Error} e
     * @method throwUncatchable
     */
    throwUncatchable: function(e) {
      _.defer(function() {
        throw e;
      });
    },


    /**
     * Throw an error.
     *
     * @param {Error} err
     */
    throwError: function(err) {
      throw err;
    },


    template: function() {
      // Temporarily change templateSettings
      // so we don't overwrite global settings
      // for other users.
      var res;
      var settings_orig = _.clone(_.templateSettings);
      _.templateSettings.interpolate = /\{(.+?)\}/g;

      res = _.template.apply(_, arguments);

      // Restore original settings
      _.templateSettings = settings_orig;

      return res;
    },

    /**
     * Invokes a callback with each object in an array.
     * Waits `interval` ms between each invocation.
     *
     * @param {Array} objects
     * @param {Function} cb
     * @param {Number} interval
     */
    eachAtInterval: function(objects, cb, interval) {
      var next = function(i) {
        var obj = objects[i];
        var nextIncremented = _.partial(next, i + 1);

        if (obj) {
          cb(obj);
          _.delay(nextIncremented, interval);
        }
      };

      next(0);
    },

    /**
     * @method tryCatch
     * @param {function()} tryFn
     * @param {function(Error)} catchFn
     */
    tryCatch: function(tryFn, catchFn) {
      try {
        tryFn();
      }
      catch (err) {
        catchFn(err);
      }
    }
  };


  // Instead of mixing our methods into underscore (which
  // would overwrite the client's window._ object), we
  // are creating a clone of underscore.
  //
  // Create a proxy _() wrapper function
  var util = function(var_args) {
    // Call the underscore wrapper with supplied
    // arguments
    var wrapper = _.apply(_, arguments);

    // Mixin custom functions
    _.each(customUtil, function(func, name) {
      wrapper[name] = function() {
        return func.call(wrapper, wrapper._wrapped);
      };
    });
    wrapper.mixin(customUtil);

    return wrapper;
  };
  _.extend(util, _, customUtil);


  return util;
});

define('jquery',{});
//     Backbone.js 1.1.2

//     (c) 2010-2014 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Backbone may be freely distributed under the MIT license.
//     For all details and documentation:
//     http://backbonejs.org

(function(root, factory) {

  // Set up Backbone appropriately for the environment. Start with AMD.
  if (typeof define === 'function' && define.amd) {
    define('backbone',['underscore', 'jquery', 'exports'], function(_, $, exports) {
      // Export global even in AMD case in case this script is loaded with
      // others that may still expect a global Backbone.
      root.Backbone = factory(root, exports, _, $);
    });

  // Next for Node.js or CommonJS. jQuery may not be needed as a module.
  } else if (typeof exports !== 'undefined') {
    var _ = require('underscore');
    factory(root, exports, _);

  // Finally, as a browser global.
  } else {
    root.Backbone = factory(root, {}, root._, (root.jQuery || root.Zepto || root.ender || root.$));
  }

}(this, function(root, Backbone, _, $) {

  // Initial Setup
  // -------------

  // Save the previous value of the `Backbone` variable, so that it can be
  // restored later on, if `noConflict` is used.
  var previousBackbone = root.Backbone;

  // Create local references to array methods we'll want to use later.
  var array = [];
  var push = array.push;
  var slice = array.slice;
  var splice = array.splice;

  // Current version of the library. Keep in sync with `package.json`.
  Backbone.VERSION = '1.1.2';

  // For Backbone's purposes, jQuery, Zepto, Ender, or My Library (kidding) owns
  // the `$` variable.
  Backbone.$ = $;

  // Runs Backbone.js in *noConflict* mode, returning the `Backbone` variable
  // to its previous owner. Returns a reference to this Backbone object.
  Backbone.noConflict = function() {
    root.Backbone = previousBackbone;
    return this;
  };

  // Turn on `emulateHTTP` to support legacy HTTP servers. Setting this option
  // will fake `"PATCH"`, `"PUT"` and `"DELETE"` requests via the `_method` parameter and
  // set a `X-Http-Method-Override` header.
  Backbone.emulateHTTP = false;

  // Turn on `emulateJSON` to support legacy servers that can't deal with direct
  // `application/json` requests ... will encode the body as
  // `application/x-www-form-urlencoded` instead and will send the model in a
  // form param named `model`.
  Backbone.emulateJSON = false;

  // Backbone.Events
  // ---------------

  // A module that can be mixed in to *any object* in order to provide it with
  // custom events. You may bind with `on` or remove with `off` callback
  // functions to an event; `trigger`-ing an event fires all callbacks in
  // succession.
  //
  //     var object = {};
  //     _.extend(object, Backbone.Events);
  //     object.on('expand', function(){ alert('expanded'); });
  //     object.trigger('expand');
  //
  var Events = Backbone.Events = {

    // Bind an event to a `callback` function. Passing `"all"` will bind
    // the callback to all events fired.
    on: function(name, callback, context) {
      if (!eventsApi(this, 'on', name, [callback, context]) || !callback) return this;
      this._events || (this._events = {});
      var events = this._events[name] || (this._events[name] = []);
      events.push({callback: callback, context: context, ctx: context || this});
      return this;
    },

    // Bind an event to only be triggered a single time. After the first time
    // the callback is invoked, it will be removed.
    once: function(name, callback, context) {
      if (!eventsApi(this, 'once', name, [callback, context]) || !callback) return this;
      var self = this;
      var once = _.once(function() {
        self.off(name, once);
        callback.apply(this, arguments);
      });
      once._callback = callback;
      return this.on(name, once, context);
    },

    // Remove one or many callbacks. If `context` is null, removes all
    // callbacks with that function. If `callback` is null, removes all
    // callbacks for the event. If `name` is null, removes all bound
    // callbacks for all events.
    off: function(name, callback, context) {
      var retain, ev, events, names, i, l, j, k;
      if (!this._events || !eventsApi(this, 'off', name, [callback, context])) return this;
      if (!name && !callback && !context) {
        this._events = void 0;
        return this;
      }
      names = name ? [name] : _.keys(this._events);
      for (i = 0, l = names.length; i < l; i++) {
        name = names[i];
        if (events = this._events[name]) {
          this._events[name] = retain = [];
          if (callback || context) {
            for (j = 0, k = events.length; j < k; j++) {
              ev = events[j];
              if ((callback && callback !== ev.callback && callback !== ev.callback._callback) ||
                  (context && context !== ev.context)) {
                retain.push(ev);
              }
            }
          }
          if (!retain.length) delete this._events[name];
        }
      }

      return this;
    },

    // Trigger one or many events, firing all bound callbacks. Callbacks are
    // passed the same arguments as `trigger` is, apart from the event name
    // (unless you're listening on `"all"`, which will cause your callback to
    // receive the true name of the event as the first argument).
    trigger: function(name) {
      if (!this._events) return this;
      var args = slice.call(arguments, 1);
      if (!eventsApi(this, 'trigger', name, args)) return this;
      var events = this._events[name];
      var allEvents = this._events.all;
      if (events) triggerEvents(events, args);
      if (allEvents) triggerEvents(allEvents, arguments);
      return this;
    },

    // Tell this object to stop listening to either specific events ... or
    // to every object it's currently listening to.
    stopListening: function(obj, name, callback) {
      var listeningTo = this._listeningTo;
      if (!listeningTo) return this;
      var remove = !name && !callback;
      if (!callback && typeof name === 'object') callback = this;
      if (obj) (listeningTo = {})[obj._listenId] = obj;
      for (var id in listeningTo) {
        obj = listeningTo[id];
        obj.off(name, callback, this);
        if (remove || _.isEmpty(obj._events)) delete this._listeningTo[id];
      }
      return this;
    }

  };

  // Regular expression used to split event strings.
  var eventSplitter = /\s+/;

  // Implement fancy features of the Events API such as multiple event
  // names `"change blur"` and jQuery-style event maps `{change: action}`
  // in terms of the existing API.
  var eventsApi = function(obj, action, name, rest) {
    if (!name) return true;

    // Handle event maps.
    if (typeof name === 'object') {
      for (var key in name) {
        obj[action].apply(obj, [key, name[key]].concat(rest));
      }
      return false;
    }

    // Handle space separated event names.
    if (eventSplitter.test(name)) {
      var names = name.split(eventSplitter);
      for (var i = 0, l = names.length; i < l; i++) {
        obj[action].apply(obj, [names[i]].concat(rest));
      }
      return false;
    }

    return true;
  };

  // A difficult-to-believe, but optimized internal dispatch function for
  // triggering events. Tries to keep the usual cases speedy (most internal
  // Backbone events have 3 arguments).
  var triggerEvents = function(events, args) {
    var ev, i = -1, l = events.length, a1 = args[0], a2 = args[1], a3 = args[2];
    switch (args.length) {
      case 0: while (++i < l) (ev = events[i]).callback.call(ev.ctx); return;
      case 1: while (++i < l) (ev = events[i]).callback.call(ev.ctx, a1); return;
      case 2: while (++i < l) (ev = events[i]).callback.call(ev.ctx, a1, a2); return;
      case 3: while (++i < l) (ev = events[i]).callback.call(ev.ctx, a1, a2, a3); return;
      default: while (++i < l) (ev = events[i]).callback.apply(ev.ctx, args); return;
    }
  };

  var listenMethods = {listenTo: 'on', listenToOnce: 'once'};

  // Inversion-of-control versions of `on` and `once`. Tell *this* object to
  // listen to an event in another object ... keeping track of what it's
  // listening to.
  _.each(listenMethods, function(implementation, method) {
    Events[method] = function(obj, name, callback) {
      var listeningTo = this._listeningTo || (this._listeningTo = {});
      var id = obj._listenId || (obj._listenId = _.uniqueId('l'));
      listeningTo[id] = obj;
      if (!callback && typeof name === 'object') callback = this;
      obj[implementation](name, callback, this);
      return this;
    };
  });

  // Aliases for backwards compatibility.
  Events.bind   = Events.on;
  Events.unbind = Events.off;

  // Allow the `Backbone` object to serve as a global event bus, for folks who
  // want global "pubsub" in a convenient place.
  _.extend(Backbone, Events);

  // Backbone.Model
  // --------------

  // Backbone **Models** are the basic data object in the framework --
  // frequently representing a row in a table in a database on your server.
  // A discrete chunk of data and a bunch of useful, related methods for
  // performing computations and transformations on that data.

  // Create a new model with the specified attributes. A client id (`cid`)
  // is automatically generated and assigned for you.
  var Model = Backbone.Model = function(attributes, options) {
    var attrs = attributes || {};
    options || (options = {});
    this.cid = _.uniqueId('c');
    this.attributes = {};
    if (options.collection) this.collection = options.collection;
    if (options.parse) attrs = this.parse(attrs, options) || {};
    attrs = _.defaults({}, attrs, _.result(this, 'defaults'));
    this.set(attrs, options);
    this.changed = {};
    this.initialize.apply(this, arguments);
  };

  // Attach all inheritable methods to the Model prototype.
  _.extend(Model.prototype, Events, {

    // A hash of attributes whose current and previous value differ.
    changed: null,

    // The value returned during the last failed validation.
    validationError: null,

    // The default name for the JSON `id` attribute is `"id"`. MongoDB and
    // CouchDB users may want to set this to `"_id"`.
    idAttribute: 'id',

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // Return a copy of the model's `attributes` object.
    toJSON: function(options) {
      return _.clone(this.attributes);
    },

    // Proxy `Backbone.sync` by default -- but override this if you need
    // custom syncing semantics for *this* particular model.
    sync: function() {
      return Backbone.sync.apply(this, arguments);
    },

    // Get the value of an attribute.
    get: function(attr) {
      return this.attributes[attr];
    },

    // Get the HTML-escaped value of an attribute.
    escape: function(attr) {
      return _.escape(this.get(attr));
    },

    // Returns `true` if the attribute contains a value that is not null
    // or undefined.
    has: function(attr) {
      return this.get(attr) != null;
    },

    // Set a hash of model attributes on the object, firing `"change"`. This is
    // the core primitive operation of a model, updating the data and notifying
    // anyone who needs to know about the change in state. The heart of the beast.
    set: function(key, val, options) {
      var attr, attrs, unset, changes, silent, changing, prev, current;
      if (key == null) return this;

      // Handle both `"key", value` and `{key: value}` -style arguments.
      if (typeof key === 'object') {
        attrs = key;
        options = val;
      } else {
        (attrs = {})[key] = val;
      }

      options || (options = {});

      // Run validation.
      if (!this._validate(attrs, options)) return false;

      // Extract attributes and options.
      unset           = options.unset;
      silent          = options.silent;
      changes         = [];
      changing        = this._changing;
      this._changing  = true;

      if (!changing) {
        this._previousAttributes = _.clone(this.attributes);
        this.changed = {};
      }
      current = this.attributes, prev = this._previousAttributes;

      // Check for changes of `id`.
      if (this.idAttribute in attrs) this.id = attrs[this.idAttribute];

      // For each `set` attribute, update or delete the current value.
      for (attr in attrs) {
        val = attrs[attr];
        if (!_.isEqual(current[attr], val)) changes.push(attr);
        if (!_.isEqual(prev[attr], val)) {
          this.changed[attr] = val;
        } else {
          delete this.changed[attr];
        }
        unset ? delete current[attr] : current[attr] = val;
      }

      // Trigger all relevant attribute changes.
      if (!silent) {
        if (changes.length) this._pending = options;
        for (var i = 0, l = changes.length; i < l; i++) {
          this.trigger('change:' + changes[i], this, current[changes[i]], options);
        }
      }

      // You might be wondering why there's a `while` loop here. Changes can
      // be recursively nested within `"change"` events.
      if (changing) return this;
      if (!silent) {
        while (this._pending) {
          options = this._pending;
          this._pending = false;
          this.trigger('change', this, options);
        }
      }
      this._pending = false;
      this._changing = false;
      return this;
    },

    // Remove an attribute from the model, firing `"change"`. `unset` is a noop
    // if the attribute doesn't exist.
    unset: function(attr, options) {
      return this.set(attr, void 0, _.extend({}, options, {unset: true}));
    },

    // Clear all attributes on the model, firing `"change"`.
    clear: function(options) {
      var attrs = {};
      for (var key in this.attributes) attrs[key] = void 0;
      return this.set(attrs, _.extend({}, options, {unset: true}));
    },

    // Determine if the model has changed since the last `"change"` event.
    // If you specify an attribute name, determine if that attribute has changed.
    hasChanged: function(attr) {
      if (attr == null) return !_.isEmpty(this.changed);
      return _.has(this.changed, attr);
    },

    // Return an object containing all the attributes that have changed, or
    // false if there are no changed attributes. Useful for determining what
    // parts of a view need to be updated and/or what attributes need to be
    // persisted to the server. Unset attributes will be set to undefined.
    // You can also pass an attributes object to diff against the model,
    // determining if there *would be* a change.
    changedAttributes: function(diff) {
      if (!diff) return this.hasChanged() ? _.clone(this.changed) : false;
      var val, changed = false;
      var old = this._changing ? this._previousAttributes : this.attributes;
      for (var attr in diff) {
        if (_.isEqual(old[attr], (val = diff[attr]))) continue;
        (changed || (changed = {}))[attr] = val;
      }
      return changed;
    },

    // Get the previous value of an attribute, recorded at the time the last
    // `"change"` event was fired.
    previous: function(attr) {
      if (attr == null || !this._previousAttributes) return null;
      return this._previousAttributes[attr];
    },

    // Get all of the attributes of the model at the time of the previous
    // `"change"` event.
    previousAttributes: function() {
      return _.clone(this._previousAttributes);
    },

    // Fetch the model from the server. If the server's representation of the
    // model differs from its current attributes, they will be overridden,
    // triggering a `"change"` event.
    fetch: function(options) {
      options = options ? _.clone(options) : {};
      if (options.parse === void 0) options.parse = true;
      var model = this;
      var success = options.success;
      options.success = function(resp) {
        if (!model.set(model.parse(resp, options), options)) return false;
        if (success) success(model, resp, options);
        model.trigger('sync', model, resp, options);
      };
      wrapError(this, options);
      return this.sync('read', this, options);
    },

    // Set a hash of model attributes, and sync the model to the server.
    // If the server returns an attributes hash that differs, the model's
    // state will be `set` again.
    save: function(key, val, options) {
      var attrs, method, xhr, attributes = this.attributes;

      // Handle both `"key", value` and `{key: value}` -style arguments.
      if (key == null || typeof key === 'object') {
        attrs = key;
        options = val;
      } else {
        (attrs = {})[key] = val;
      }

      options = _.extend({validate: true}, options);

      // If we're not waiting and attributes exist, save acts as
      // `set(attr).save(null, opts)` with validation. Otherwise, check if
      // the model will be valid when the attributes, if any, are set.
      if (attrs && !options.wait) {
        if (!this.set(attrs, options)) return false;
      } else {
        if (!this._validate(attrs, options)) return false;
      }

      // Set temporary attributes if `{wait: true}`.
      if (attrs && options.wait) {
        this.attributes = _.extend({}, attributes, attrs);
      }

      // After a successful server-side save, the client is (optionally)
      // updated with the server-side state.
      if (options.parse === void 0) options.parse = true;
      var model = this;
      var success = options.success;
      options.success = function(resp) {
        // Ensure attributes are restored during synchronous saves.
        model.attributes = attributes;
        var serverAttrs = model.parse(resp, options);
        if (options.wait) serverAttrs = _.extend(attrs || {}, serverAttrs);
        if (_.isObject(serverAttrs) && !model.set(serverAttrs, options)) {
          return false;
        }
        if (success) success(model, resp, options);
        model.trigger('sync', model, resp, options);
      };
      wrapError(this, options);

      method = this.isNew() ? 'create' : (options.patch ? 'patch' : 'update');
      if (method === 'patch') options.attrs = attrs;
      xhr = this.sync(method, this, options);

      // Restore attributes.
      if (attrs && options.wait) this.attributes = attributes;

      return xhr;
    },

    // Destroy this model on the server if it was already persisted.
    // Optimistically removes the model from its collection, if it has one.
    // If `wait: true` is passed, waits for the server to respond before removal.
    destroy: function(options) {
      options = options ? _.clone(options) : {};
      var model = this;
      var success = options.success;

      var destroy = function() {
        model.trigger('destroy', model, model.collection, options);
      };

      options.success = function(resp) {
        if (options.wait || model.isNew()) destroy();
        if (success) success(model, resp, options);
        if (!model.isNew()) model.trigger('sync', model, resp, options);
      };

      if (this.isNew()) {
        options.success();
        return false;
      }
      wrapError(this, options);

      var xhr = this.sync('delete', this, options);
      if (!options.wait) destroy();
      return xhr;
    },

    // Default URL for the model's representation on the server -- if you're
    // using Backbone's restful methods, override this to change the endpoint
    // that will be called.
    url: function() {
      var base =
        _.result(this, 'urlRoot') ||
        _.result(this.collection, 'url') ||
        urlError();
      if (this.isNew()) return base;
      return base.replace(/([^\/])$/, '$1/') + encodeURIComponent(this.id);
    },

    // **parse** converts a response into the hash of attributes to be `set` on
    // the model. The default implementation is just to pass the response along.
    parse: function(resp, options) {
      return resp;
    },

    // Create a new model with identical attributes to this one.
    clone: function() {
      return new this.constructor(this.attributes);
    },

    // A model is new if it has never been saved to the server, and lacks an id.
    isNew: function() {
      return !this.has(this.idAttribute);
    },

    // Check if the model is currently in a valid state.
    isValid: function(options) {
      return this._validate({}, _.extend(options || {}, { validate: true }));
    },

    // Run validation against the next complete set of model attributes,
    // returning `true` if all is well. Otherwise, fire an `"invalid"` event.
    _validate: function(attrs, options) {
      if (!options.validate || !this.validate) return true;
      attrs = _.extend({}, this.attributes, attrs);
      var error = this.validationError = this.validate(attrs, options) || null;
      if (!error) return true;
      this.trigger('invalid', this, error, _.extend(options, {validationError: error}));
      return false;
    }

  });

  // Underscore methods that we want to implement on the Model.
  var modelMethods = ['keys', 'values', 'pairs', 'invert', 'pick', 'omit'];

  // Mix in each Underscore method as a proxy to `Model#attributes`.
  _.each(modelMethods, function(method) {
    Model.prototype[method] = function() {
      var args = slice.call(arguments);
      args.unshift(this.attributes);
      return _[method].apply(_, args);
    };
  });

  // Backbone.Collection
  // -------------------

  // If models tend to represent a single row of data, a Backbone Collection is
  // more analagous to a table full of data ... or a small slice or page of that
  // table, or a collection of rows that belong together for a particular reason
  // -- all of the messages in this particular folder, all of the documents
  // belonging to this particular author, and so on. Collections maintain
  // indexes of their models, both in order, and for lookup by `id`.

  // Create a new **Collection**, perhaps to contain a specific type of `model`.
  // If a `comparator` is specified, the Collection will maintain
  // its models in sort order, as they're added and removed.
  var Collection = Backbone.Collection = function(models, options) {
    options || (options = {});
    if (options.model) this.model = options.model;
    if (options.comparator !== void 0) this.comparator = options.comparator;
    this._reset();
    this.initialize.apply(this, arguments);
    if (models) this.reset(models, _.extend({silent: true}, options));
  };

  // Default options for `Collection#set`.
  var setOptions = {add: true, remove: true, merge: true};
  var addOptions = {add: true, remove: false};

  // Define the Collection's inheritable methods.
  _.extend(Collection.prototype, Events, {

    // The default model for a collection is just a **Backbone.Model**.
    // This should be overridden in most cases.
    model: Model,

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // The JSON representation of a Collection is an array of the
    // models' attributes.
    toJSON: function(options) {
      return this.map(function(model){ return model.toJSON(options); });
    },

    // Proxy `Backbone.sync` by default.
    sync: function() {
      return Backbone.sync.apply(this, arguments);
    },

    // Add a model, or list of models to the set.
    add: function(models, options) {
      return this.set(models, _.extend({merge: false}, options, addOptions));
    },

    // Remove a model, or a list of models from the set.
    remove: function(models, options) {
      var singular = !_.isArray(models);
      models = singular ? [models] : _.clone(models);
      options || (options = {});
      var i, l, index, model;
      for (i = 0, l = models.length; i < l; i++) {
        model = models[i] = this.get(models[i]);
        if (!model) continue;
        delete this._byId[model.id];
        delete this._byId[model.cid];
        index = this.indexOf(model);
        this.models.splice(index, 1);
        this.length--;
        if (!options.silent) {
          options.index = index;
          model.trigger('remove', model, this, options);
        }
        this._removeReference(model, options);
      }
      return singular ? models[0] : models;
    },

    // Update a collection by `set`-ing a new list of models, adding new ones,
    // removing models that are no longer present, and merging models that
    // already exist in the collection, as necessary. Similar to **Model#set**,
    // the core operation for updating the data contained by the collection.
    set: function(models, options) {
      options = _.defaults({}, options, setOptions);
      if (options.parse) models = this.parse(models, options);
      var singular = !_.isArray(models);
      models = singular ? (models ? [models] : []) : _.clone(models);
      var i, l, id, model, attrs, existing, sort;
      var at = options.at;
      var targetModel = this.model;
      var sortable = this.comparator && (at == null) && options.sort !== false;
      var sortAttr = _.isString(this.comparator) ? this.comparator : null;
      var toAdd = [], toRemove = [], modelMap = {};
      var add = options.add, merge = options.merge, remove = options.remove;
      var order = !sortable && add && remove ? [] : false;

      // Turn bare objects into model references, and prevent invalid models
      // from being added.
      for (i = 0, l = models.length; i < l; i++) {
        attrs = models[i] || {};
        if (attrs instanceof Model) {
          id = model = attrs;
        } else {
          id = attrs[targetModel.prototype.idAttribute || 'id'];
        }

        // If a duplicate is found, prevent it from being added and
        // optionally merge it into the existing model.
        if (existing = this.get(id)) {
          if (remove) modelMap[existing.cid] = true;
          if (merge) {
            attrs = attrs === model ? model.attributes : attrs;
            if (options.parse) attrs = existing.parse(attrs, options);
            existing.set(attrs, options);
            if (sortable && !sort && existing.hasChanged(sortAttr)) sort = true;
          }
          models[i] = existing;

        // If this is a new, valid model, push it to the `toAdd` list.
        } else if (add) {
          model = models[i] = this._prepareModel(attrs, options);
          if (!model) continue;
          toAdd.push(model);
          this._addReference(model, options);
        }

        // Do not add multiple models with the same `id`.
        model = existing || model;
        if (order && (model.isNew() || !modelMap[model.id])) order.push(model);
        modelMap[model.id] = true;
      }

      // Remove nonexistent models if appropriate.
      if (remove) {
        for (i = 0, l = this.length; i < l; ++i) {
          if (!modelMap[(model = this.models[i]).cid]) toRemove.push(model);
        }
        if (toRemove.length) this.remove(toRemove, options);
      }

      // See if sorting is needed, update `length` and splice in new models.
      if (toAdd.length || (order && order.length)) {
        if (sortable) sort = true;
        this.length += toAdd.length;
        if (at != null) {
          for (i = 0, l = toAdd.length; i < l; i++) {
            this.models.splice(at + i, 0, toAdd[i]);
          }
        } else {
          if (order) this.models.length = 0;
          var orderedModels = order || toAdd;
          for (i = 0, l = orderedModels.length; i < l; i++) {
            this.models.push(orderedModels[i]);
          }
        }
      }

      // Silently sort the collection if appropriate.
      if (sort) this.sort({silent: true});

      // Unless silenced, it's time to fire all appropriate add/sort events.
      if (!options.silent) {
        for (i = 0, l = toAdd.length; i < l; i++) {
          (model = toAdd[i]).trigger('add', model, this, options);
        }
        if (sort || (order && order.length)) this.trigger('sort', this, options);
      }

      // Return the added (or merged) model (or models).
      return singular ? models[0] : models;
    },

    // When you have more items than you want to add or remove individually,
    // you can reset the entire set with a new list of models, without firing
    // any granular `add` or `remove` events. Fires `reset` when finished.
    // Useful for bulk operations and optimizations.
    reset: function(models, options) {
      options || (options = {});
      for (var i = 0, l = this.models.length; i < l; i++) {
        this._removeReference(this.models[i], options);
      }
      options.previousModels = this.models;
      this._reset();
      models = this.add(models, _.extend({silent: true}, options));
      if (!options.silent) this.trigger('reset', this, options);
      return models;
    },

    // Add a model to the end of the collection.
    push: function(model, options) {
      return this.add(model, _.extend({at: this.length}, options));
    },

    // Remove a model from the end of the collection.
    pop: function(options) {
      var model = this.at(this.length - 1);
      this.remove(model, options);
      return model;
    },

    // Add a model to the beginning of the collection.
    unshift: function(model, options) {
      return this.add(model, _.extend({at: 0}, options));
    },

    // Remove a model from the beginning of the collection.
    shift: function(options) {
      var model = this.at(0);
      this.remove(model, options);
      return model;
    },

    // Slice out a sub-array of models from the collection.
    slice: function() {
      return slice.apply(this.models, arguments);
    },

    // Get a model from the set by id.
    get: function(obj) {
      if (obj == null) return void 0;
      return this._byId[obj] || this._byId[obj.id] || this._byId[obj.cid];
    },

    // Get the model at the given index.
    at: function(index) {
      return this.models[index];
    },

    // Return models with matching attributes. Useful for simple cases of
    // `filter`.
    where: function(attrs, first) {
      if (_.isEmpty(attrs)) return first ? void 0 : [];
      return this[first ? 'find' : 'filter'](function(model) {
        for (var key in attrs) {
          if (attrs[key] !== model.get(key)) return false;
        }
        return true;
      });
    },

    // Return the first model with matching attributes. Useful for simple cases
    // of `find`.
    findWhere: function(attrs) {
      return this.where(attrs, true);
    },

    // Force the collection to re-sort itself. You don't need to call this under
    // normal circumstances, as the set will maintain sort order as each item
    // is added.
    sort: function(options) {
      if (!this.comparator) throw new Error('Cannot sort a set without a comparator');
      options || (options = {});

      // Run sort based on type of `comparator`.
      if (_.isString(this.comparator) || this.comparator.length === 1) {
        this.models = this.sortBy(this.comparator, this);
      } else {
        this.models.sort(_.bind(this.comparator, this));
      }

      if (!options.silent) this.trigger('sort', this, options);
      return this;
    },

    // Pluck an attribute from each model in the collection.
    pluck: function(attr) {
      return _.invoke(this.models, 'get', attr);
    },

    // Fetch the default set of models for this collection, resetting the
    // collection when they arrive. If `reset: true` is passed, the response
    // data will be passed through the `reset` method instead of `set`.
    fetch: function(options) {
      options = options ? _.clone(options) : {};
      if (options.parse === void 0) options.parse = true;
      var success = options.success;
      var collection = this;
      options.success = function(resp) {
        var method = options.reset ? 'reset' : 'set';
        collection[method](resp, options);
        if (success) success(collection, resp, options);
        collection.trigger('sync', collection, resp, options);
      };
      wrapError(this, options);
      return this.sync('read', this, options);
    },

    // Create a new instance of a model in this collection. Add the model to the
    // collection immediately, unless `wait: true` is passed, in which case we
    // wait for the server to agree.
    create: function(model, options) {
      options = options ? _.clone(options) : {};
      if (!(model = this._prepareModel(model, options))) return false;
      if (!options.wait) this.add(model, options);
      var collection = this;
      var success = options.success;
      options.success = function(model, resp) {
        if (options.wait) collection.add(model, options);
        if (success) success(model, resp, options);
      };
      model.save(null, options);
      return model;
    },

    // **parse** converts a response into a list of models to be added to the
    // collection. The default implementation is just to pass it through.
    parse: function(resp, options) {
      return resp;
    },

    // Create a new collection with an identical list of models as this one.
    clone: function() {
      return new this.constructor(this.models);
    },

    // Private method to reset all internal state. Called when the collection
    // is first initialized or reset.
    _reset: function() {
      this.length = 0;
      this.models = [];
      this._byId  = {};
    },

    // Prepare a hash of attributes (or other model) to be added to this
    // collection.
    _prepareModel: function(attrs, options) {
      if (attrs instanceof Model) return attrs;
      options = options ? _.clone(options) : {};
      options.collection = this;
      var model = new this.model(attrs, options);
      if (!model.validationError) return model;
      this.trigger('invalid', this, model.validationError, options);
      return false;
    },

    // Internal method to create a model's ties to a collection.
    _addReference: function(model, options) {
      this._byId[model.cid] = model;
      if (model.id != null) this._byId[model.id] = model;
      if (!model.collection) model.collection = this;
      model.on('all', this._onModelEvent, this);
    },

    // Internal method to sever a model's ties to a collection.
    _removeReference: function(model, options) {
      if (this === model.collection) delete model.collection;
      model.off('all', this._onModelEvent, this);
    },

    // Internal method called every time a model in the set fires an event.
    // Sets need to update their indexes when models change ids. All other
    // events simply proxy through. "add" and "remove" events that originate
    // in other collections are ignored.
    _onModelEvent: function(event, model, collection, options) {
      if ((event === 'add' || event === 'remove') && collection !== this) return;
      if (event === 'destroy') this.remove(model, options);
      if (model && event === 'change:' + model.idAttribute) {
        delete this._byId[model.previous(model.idAttribute)];
        if (model.id != null) this._byId[model.id] = model;
      }
      this.trigger.apply(this, arguments);
    }

  });

  // Underscore methods that we want to implement on the Collection.
  // 90% of the core usefulness of Backbone Collections is actually implemented
  // right here:
  var methods = ['forEach', 'each', 'map', 'collect', 'reduce', 'foldl',
    'inject', 'reduceRight', 'foldr', 'find', 'detect', 'filter', 'select',
    'reject', 'every', 'all', 'some', 'any', 'include', 'contains', 'invoke',
    'max', 'min', 'toArray', 'size', 'first', 'head', 'take', 'initial', 'rest',
    'tail', 'drop', 'last', 'without', 'difference', 'indexOf', 'shuffle',
    'lastIndexOf', 'isEmpty', 'chain', 'sample'];

  // Mix in each Underscore method as a proxy to `Collection#models`.
  _.each(methods, function(method) {
    Collection.prototype[method] = function() {
      var args = slice.call(arguments);
      args.unshift(this.models);
      return _[method].apply(_, args);
    };
  });

  // Underscore methods that take a property name as an argument.
  var attributeMethods = ['groupBy', 'countBy', 'sortBy', 'indexBy'];

  // Use attributes instead of properties.
  _.each(attributeMethods, function(method) {
    Collection.prototype[method] = function(value, context) {
      var iterator = _.isFunction(value) ? value : function(model) {
        return model.get(value);
      };
      return _[method](this.models, iterator, context);
    };
  });

  // Backbone.View
  // -------------

  // Backbone Views are almost more convention than they are actual code. A View
  // is simply a JavaScript object that represents a logical chunk of UI in the
  // DOM. This might be a single item, an entire list, a sidebar or panel, or
  // even the surrounding frame which wraps your whole app. Defining a chunk of
  // UI as a **View** allows you to define your DOM events declaratively, without
  // having to worry about render order ... and makes it easy for the view to
  // react to specific changes in the state of your models.

  // Creating a Backbone.View creates its initial element outside of the DOM,
  // if an existing element is not provided...
  var View = Backbone.View = function(options) {
    this.cid = _.uniqueId('view');
    options || (options = {});
    _.extend(this, _.pick(options, viewOptions));
    this._ensureElement();
    this.initialize.apply(this, arguments);
    this.delegateEvents();
  };

  // Cached regex to split keys for `delegate`.
  var delegateEventSplitter = /^(\S+)\s*(.*)$/;

  // List of view options to be merged as properties.
  var viewOptions = ['model', 'collection', 'el', 'id', 'attributes', 'className', 'tagName', 'events'];

  // Set up all inheritable **Backbone.View** properties and methods.
  _.extend(View.prototype, Events, {

    // The default `tagName` of a View's element is `"div"`.
    tagName: 'div',

    // jQuery delegate for element lookup, scoped to DOM elements within the
    // current view. This should be preferred to global lookups where possible.
    $: function(selector) {
      return this.$el.find(selector);
    },

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // **render** is the core function that your view should override, in order
    // to populate its element (`this.el`), with the appropriate HTML. The
    // convention is for **render** to always return `this`.
    render: function() {
      return this;
    },

    // Remove this view by taking the element out of the DOM, and removing any
    // applicable Backbone.Events listeners.
    remove: function() {
      this.$el.remove();
      this.stopListening();
      return this;
    },

    // Change the view's element (`this.el` property), including event
    // re-delegation.
    setElement: function(element, delegate) {
      if (this.$el) this.undelegateEvents();
      this.$el = element instanceof Backbone.$ ? element : Backbone.$(element);
      this.el = this.$el[0];
      if (delegate !== false) this.delegateEvents();
      return this;
    },

    // Set callbacks, where `this.events` is a hash of
    //
    // *{"event selector": "callback"}*
    //
    //     {
    //       'mousedown .title':  'edit',
    //       'click .button':     'save',
    //       'click .open':       function(e) { ... }
    //     }
    //
    // pairs. Callbacks will be bound to the view, with `this` set properly.
    // Uses event delegation for efficiency.
    // Omitting the selector binds the event to `this.el`.
    // This only works for delegate-able events: not `focus`, `blur`, and
    // not `change`, `submit`, and `reset` in Internet Explorer.
    delegateEvents: function(events) {
      if (!(events || (events = _.result(this, 'events')))) return this;
      this.undelegateEvents();
      for (var key in events) {
        var method = events[key];
        if (!_.isFunction(method)) method = this[events[key]];
        if (!method) continue;

        var match = key.match(delegateEventSplitter);
        var eventName = match[1], selector = match[2];
        method = _.bind(method, this);
        eventName += '.delegateEvents' + this.cid;
        if (selector === '') {
          this.$el.on(eventName, method);
        } else {
          this.$el.on(eventName, selector, method);
        }
      }
      return this;
    },

    // Clears all callbacks previously bound to the view with `delegateEvents`.
    // You usually don't need to use this, but may wish to if you have multiple
    // Backbone views attached to the same DOM element.
    undelegateEvents: function() {
      this.$el.off('.delegateEvents' + this.cid);
      return this;
    },

    // Ensure that the View has a DOM element to render into.
    // If `this.el` is a string, pass it through `$()`, take the first
    // matching element, and re-assign it to `el`. Otherwise, create
    // an element from the `id`, `className` and `tagName` properties.
    _ensureElement: function() {
      if (!this.el) {
        var attrs = _.extend({}, _.result(this, 'attributes'));
        if (this.id) attrs.id = _.result(this, 'id');
        if (this.className) attrs['class'] = _.result(this, 'className');
        var $el = Backbone.$('<' + _.result(this, 'tagName') + '>').attr(attrs);
        this.setElement($el, false);
      } else {
        this.setElement(_.result(this, 'el'), false);
      }
    }

  });

  // Backbone.sync
  // -------------

  // Override this function to change the manner in which Backbone persists
  // models to the server. You will be passed the type of request, and the
  // model in question. By default, makes a RESTful Ajax request
  // to the model's `url()`. Some possible customizations could be:
  //
  // * Use `setTimeout` to batch rapid-fire updates into a single request.
  // * Send up the models as XML instead of JSON.
  // * Persist models via WebSockets instead of Ajax.
  //
  // Turn on `Backbone.emulateHTTP` in order to send `PUT` and `DELETE` requests
  // as `POST`, with a `_method` parameter containing the true HTTP method,
  // as well as all requests with the body as `application/x-www-form-urlencoded`
  // instead of `application/json` with the model in a param named `model`.
  // Useful when interfacing with server-side languages like **PHP** that make
  // it difficult to read the body of `PUT` requests.
  Backbone.sync = function(method, model, options) {
    var type = methodMap[method];

    // Default options, unless specified.
    _.defaults(options || (options = {}), {
      emulateHTTP: Backbone.emulateHTTP,
      emulateJSON: Backbone.emulateJSON
    });

    // Default JSON-request options.
    var params = {type: type, dataType: 'json'};

    // Ensure that we have a URL.
    if (!options.url) {
      params.url = _.result(model, 'url') || urlError();
    }

    // Ensure that we have the appropriate request data.
    if (options.data == null && model && (method === 'create' || method === 'update' || method === 'patch')) {
      params.contentType = 'application/json';
      params.data = JSON.stringify(options.attrs || model.toJSON(options));
    }

    // For older servers, emulate JSON by encoding the request into an HTML-form.
    if (options.emulateJSON) {
      params.contentType = 'application/x-www-form-urlencoded';
      params.data = params.data ? {model: params.data} : {};
    }

    // For older servers, emulate HTTP by mimicking the HTTP method with `_method`
    // And an `X-HTTP-Method-Override` header.
    if (options.emulateHTTP && (type === 'PUT' || type === 'DELETE' || type === 'PATCH')) {
      params.type = 'POST';
      if (options.emulateJSON) params.data._method = type;
      var beforeSend = options.beforeSend;
      options.beforeSend = function(xhr) {
        xhr.setRequestHeader('X-HTTP-Method-Override', type);
        if (beforeSend) return beforeSend.apply(this, arguments);
      };
    }

    // Don't process data on a non-GET request.
    if (params.type !== 'GET' && !options.emulateJSON) {
      params.processData = false;
    }

    // If we're sending a `PATCH` request, and we're in an old Internet Explorer
    // that still has ActiveX enabled by default, override jQuery to use that
    // for XHR instead. Remove this line when jQuery supports `PATCH` on IE8.
    if (params.type === 'PATCH' && noXhrPatch) {
      params.xhr = function() {
        return new ActiveXObject("Microsoft.XMLHTTP");
      };
    }

    // Make the request, allowing the user to override any Ajax options.
    var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
    model.trigger('request', model, xhr, options);
    return xhr;
  };

  var noXhrPatch =
    typeof window !== 'undefined' && !!window.ActiveXObject &&
      !(window.XMLHttpRequest && (new XMLHttpRequest).dispatchEvent);

  // Map from CRUD to HTTP for our default `Backbone.sync` implementation.
  var methodMap = {
    'create': 'POST',
    'update': 'PUT',
    'patch':  'PATCH',
    'delete': 'DELETE',
    'read':   'GET'
  };

  // Set the default implementation of `Backbone.ajax` to proxy through to `$`.
  // Override this if you'd like to use a different library.
  Backbone.ajax = function() {
    return Backbone.$.ajax.apply(Backbone.$, arguments);
  };

  // Backbone.Router
  // ---------------

  // Routers map faux-URLs to actions, and fire events when routes are
  // matched. Creating a new one sets its `routes` hash, if not set statically.
  var Router = Backbone.Router = function(options) {
    options || (options = {});
    if (options.routes) this.routes = options.routes;
    this._bindRoutes();
    this.initialize.apply(this, arguments);
  };

  // Cached regular expressions for matching named param parts and splatted
  // parts of route strings.
  var optionalParam = /\((.*?)\)/g;
  var namedParam    = /(\(\?)?:\w+/g;
  var splatParam    = /\*\w+/g;
  var escapeRegExp  = /[\-{}\[\]+?.,\\\^$|#\s]/g;

  // Set up all inheritable **Backbone.Router** properties and methods.
  _.extend(Router.prototype, Events, {

    // Initialize is an empty function by default. Override it with your own
    // initialization logic.
    initialize: function(){},

    // Manually bind a single named route to a callback. For example:
    //
    //     this.route('search/:query/p:num', 'search', function(query, num) {
    //       ...
    //     });
    //
    route: function(route, name, callback) {
      if (!_.isRegExp(route)) route = this._routeToRegExp(route);
      if (_.isFunction(name)) {
        callback = name;
        name = '';
      }
      if (!callback) callback = this[name];
      var router = this;
      Backbone.history.route(route, function(fragment) {
        var args = router._extractParameters(route, fragment);
        router.execute(callback, args);
        router.trigger.apply(router, ['route:' + name].concat(args));
        router.trigger('route', name, args);
        Backbone.history.trigger('route', router, name, args);
      });
      return this;
    },

    // Execute a route handler with the provided parameters.  This is an
    // excellent place to do pre-route setup or post-route cleanup.
    execute: function(callback, args) {
      if (callback) callback.apply(this, args);
    },

    // Simple proxy to `Backbone.history` to save a fragment into the history.
    navigate: function(fragment, options) {
      Backbone.history.navigate(fragment, options);
      return this;
    },

    // Bind all defined routes to `Backbone.history`. We have to reverse the
    // order of the routes here to support behavior where the most general
    // routes can be defined at the bottom of the route map.
    _bindRoutes: function() {
      if (!this.routes) return;
      this.routes = _.result(this, 'routes');
      var route, routes = _.keys(this.routes);
      while ((route = routes.pop()) != null) {
        this.route(route, this.routes[route]);
      }
    },

    // Convert a route string into a regular expression, suitable for matching
    // against the current location hash.
    _routeToRegExp: function(route) {
      route = route.replace(escapeRegExp, '\\$&')
                   .replace(optionalParam, '(?:$1)?')
                   .replace(namedParam, function(match, optional) {
                     return optional ? match : '([^/?]+)';
                   })
                   .replace(splatParam, '([^?]*?)');
      return new RegExp('^' + route + '(?:\\?([\\s\\S]*))?$');
    },

    // Given a route, and a URL fragment that it matches, return the array of
    // extracted decoded parameters. Empty or unmatched parameters will be
    // treated as `null` to normalize cross-browser behavior.
    _extractParameters: function(route, fragment) {
      var params = route.exec(fragment).slice(1);
      return _.map(params, function(param, i) {
        // Don't decode the search params.
        if (i === params.length - 1) return param || null;
        return param ? decodeURIComponent(param) : null;
      });
    }

  });

  // Backbone.History
  // ----------------

  // Handles cross-browser history management, based on either
  // [pushState](http://diveintohtml5.info/history.html) and real URLs, or
  // [onhashchange](https://developer.mozilla.org/en-US/docs/DOM/window.onhashchange)
  // and URL fragments. If the browser supports neither (old IE, natch),
  // falls back to polling.
  var History = Backbone.History = function() {
    this.handlers = [];
    _.bindAll(this, 'checkUrl');

    // Ensure that `History` can be used outside of the browser.
    if (typeof window !== 'undefined') {
      this.location = window.location;
      this.history = window.history;
    }
  };

  // Cached regex for stripping a leading hash/slash and trailing space.
  var routeStripper = /^[#\/]|\s+$/g;

  // Cached regex for stripping leading and trailing slashes.
  var rootStripper = /^\/+|\/+$/g;

  // Cached regex for detecting MSIE.
  var isExplorer = /msie [\w.]+/;

  // Cached regex for removing a trailing slash.
  var trailingSlash = /\/$/;

  // Cached regex for stripping urls of hash.
  var pathStripper = /#.*$/;

  // Has the history handling already been started?
  History.started = false;

  // Set up all inheritable **Backbone.History** properties and methods.
  _.extend(History.prototype, Events, {

    // The default interval to poll for hash changes, if necessary, is
    // twenty times a second.
    interval: 50,

    // Are we at the app root?
    atRoot: function() {
      return this.location.pathname.replace(/[^\/]$/, '$&/') === this.root;
    },

    // Gets the true hash value. Cannot use location.hash directly due to bug
    // in Firefox where location.hash will always be decoded.
    getHash: function(window) {
      var match = (window || this).location.href.match(/#(.*)$/);
      return match ? match[1] : '';
    },

    // Get the cross-browser normalized URL fragment, either from the URL,
    // the hash, or the override.
    getFragment: function(fragment, forcePushState) {
      if (fragment == null) {
        if (this._hasPushState || !this._wantsHashChange || forcePushState) {
          fragment = decodeURI(this.location.pathname + this.location.search);
          var root = this.root.replace(trailingSlash, '');
          if (!fragment.indexOf(root)) fragment = fragment.slice(root.length);
        } else {
          fragment = this.getHash();
        }
      }
      return fragment.replace(routeStripper, '');
    },

    // Start the hash change handling, returning `true` if the current URL matches
    // an existing route, and `false` otherwise.
    start: function(options) {
      if (History.started) throw new Error("Backbone.history has already been started");
      History.started = true;

      // Figure out the initial configuration. Do we need an iframe?
      // Is pushState desired ... is it available?
      this.options          = _.extend({root: '/'}, this.options, options);
      this.root             = this.options.root;
      this._wantsHashChange = this.options.hashChange !== false;
      this._wantsPushState  = !!this.options.pushState;
      this._hasPushState    = !!(this.options.pushState && this.history && this.history.pushState);
      var fragment          = this.getFragment();
      var docMode           = document.documentMode;
      var oldIE             = (isExplorer.exec(navigator.userAgent.toLowerCase()) && (!docMode || docMode <= 7));

      // Normalize root to always include a leading and trailing slash.
      this.root = ('/' + this.root + '/').replace(rootStripper, '/');

      if (oldIE && this._wantsHashChange) {
        var frame = Backbone.$('<iframe src="javascript:0" tabindex="-1">');
        this.iframe = frame.hide().appendTo('body')[0].contentWindow;
        this.navigate(fragment);
      }

      // Depending on whether we're using pushState or hashes, and whether
      // 'onhashchange' is supported, determine how we check the URL state.
      if (this._hasPushState) {
        Backbone.$(window).on('popstate', this.checkUrl);
      } else if (this._wantsHashChange && ('onhashchange' in window) && !oldIE) {
        Backbone.$(window).on('hashchange', this.checkUrl);
      } else if (this._wantsHashChange) {
        this._checkUrlInterval = setInterval(this.checkUrl, this.interval);
      }

      // Determine if we need to change the base url, for a pushState link
      // opened by a non-pushState browser.
      this.fragment = fragment;
      var loc = this.location;

      // Transition from hashChange to pushState or vice versa if both are
      // requested.
      if (this._wantsHashChange && this._wantsPushState) {

        // If we've started off with a route from a `pushState`-enabled
        // browser, but we're currently in a browser that doesn't support it...
        if (!this._hasPushState && !this.atRoot()) {
          this.fragment = this.getFragment(null, true);
          this.location.replace(this.root + '#' + this.fragment);
          // Return immediately as browser will do redirect to new url
          return true;

        // Or if we've started out with a hash-based route, but we're currently
        // in a browser where it could be `pushState`-based instead...
        } else if (this._hasPushState && this.atRoot() && loc.hash) {
          this.fragment = this.getHash().replace(routeStripper, '');
          this.history.replaceState({}, document.title, this.root + this.fragment);
        }

      }

      if (!this.options.silent) return this.loadUrl();
    },

    // Disable Backbone.history, perhaps temporarily. Not useful in a real app,
    // but possibly useful for unit testing Routers.
    stop: function() {
      Backbone.$(window).off('popstate', this.checkUrl).off('hashchange', this.checkUrl);
      if (this._checkUrlInterval) clearInterval(this._checkUrlInterval);
      History.started = false;
    },

    // Add a route to be tested when the fragment changes. Routes added later
    // may override previous routes.
    route: function(route, callback) {
      this.handlers.unshift({route: route, callback: callback});
    },

    // Checks the current URL to see if it has changed, and if it has,
    // calls `loadUrl`, normalizing across the hidden iframe.
    checkUrl: function(e) {
      var current = this.getFragment();
      if (current === this.fragment && this.iframe) {
        current = this.getFragment(this.getHash(this.iframe));
      }
      if (current === this.fragment) return false;
      if (this.iframe) this.navigate(current);
      this.loadUrl();
    },

    // Attempt to load the current URL fragment. If a route succeeds with a
    // match, returns `true`. If no defined routes matches the fragment,
    // returns `false`.
    loadUrl: function(fragment) {
      fragment = this.fragment = this.getFragment(fragment);
      return _.any(this.handlers, function(handler) {
        if (handler.route.test(fragment)) {
          handler.callback(fragment);
          return true;
        }
      });
    },

    // Save a fragment into the hash history, or replace the URL state if the
    // 'replace' option is passed. You are responsible for properly URL-encoding
    // the fragment in advance.
    //
    // The options object can contain `trigger: true` if you wish to have the
    // route callback be fired (not usually desirable), or `replace: true`, if
    // you wish to modify the current URL without adding an entry to the history.
    navigate: function(fragment, options) {
      if (!History.started) return false;
      if (!options || options === true) options = {trigger: !!options};

      var url = this.root + (fragment = this.getFragment(fragment || ''));

      // Strip the hash for matching.
      fragment = fragment.replace(pathStripper, '');

      if (this.fragment === fragment) return;
      this.fragment = fragment;

      // Don't include a trailing slash on the root.
      if (fragment === '' && url !== '/') url = url.slice(0, -1);

      // If pushState is available, we use it to set the fragment as a real URL.
      if (this._hasPushState) {
        this.history[options.replace ? 'replaceState' : 'pushState']({}, document.title, url);

      // If hash changes haven't been explicitly disabled, update the hash
      // fragment to store history.
      } else if (this._wantsHashChange) {
        this._updateHash(this.location, fragment, options.replace);
        if (this.iframe && (fragment !== this.getFragment(this.getHash(this.iframe)))) {
          // Opening and closing the iframe tricks IE7 and earlier to push a
          // history entry on hash-tag change.  When replace is true, we don't
          // want this.
          if(!options.replace) this.iframe.document.open().close();
          this._updateHash(this.iframe.location, fragment, options.replace);
        }

      // If you've told us that you explicitly don't want fallback hashchange-
      // based history, then `navigate` becomes a page refresh.
      } else {
        return this.location.assign(url);
      }
      if (options.trigger) return this.loadUrl(fragment);
    },

    // Update the hash location, either replacing the current entry, or adding
    // a new one to the browser history.
    _updateHash: function(location, fragment, replace) {
      if (replace) {
        var href = location.href.replace(/(javascript:|#).*$/, '');
        location.replace(href + '#' + fragment);
      } else {
        // Some browsers require that `hash` contains a leading #.
        location.hash = '#' + fragment;
      }
    }

  });

  // Create the default Backbone.history.
  Backbone.history = new History;

  // Helpers
  // -------

  // Helper function to correctly set up the prototype chain, for subclasses.
  // Similar to `goog.inherits`, but uses a hash of prototype properties and
  // class properties to be extended.
  var extend = function(protoProps, staticProps) {
    var parent = this;
    var child;

    // The constructor function for the new subclass is either defined by you
    // (the "constructor" property in your `extend` definition), or defaulted
    // by us to simply call the parent's constructor.
    if (protoProps && _.has(protoProps, 'constructor')) {
      child = protoProps.constructor;
    } else {
      child = function(){ return parent.apply(this, arguments); };
    }

    // Add static properties to the constructor function, if supplied.
    _.extend(child, parent, staticProps);

    // Set the prototype chain to inherit from `parent`, without calling
    // `parent`'s constructor function.
    var Surrogate = function(){ this.constructor = child; };
    Surrogate.prototype = parent.prototype;
    child.prototype = new Surrogate;

    // Add prototype properties (instance properties) to the subclass,
    // if supplied.
    if (protoProps) _.extend(child.prototype, protoProps);

    // Set a convenience property in case the parent's prototype is needed
    // later.
    child.__super__ = parent.prototype;

    return child;
  };

  // Set up inheritance for the model, collection, router, view and history.
  Model.extend = Collection.extend = Router.extend = View.extend = History.extend = extend;

  // Throw an error when a URL is needed, and none is supplied.
  var urlError = function() {
    throw new Error('A "url" property or function must be specified');
  };

  // Wrap an optional error callback with a fallback error event.
  var wrapError = function(model, options) {
    var error = options.error;
    options.error = function(resp) {
      if (error) error(model, resp, options);
      model.trigger('error', model, resp, options);
    };
  };

  return Backbone;

}));

define('aeris/events',['aeris/util', 'backbone'], function(_, Backbone) {
  /**
   * An events manager,
   * based on Backbone.Events.
   *
   * @class aeris.Events
   * @extends Backbone.Events
   * @publicApi
   * @constructor
   */
  var Events = function() {
  };

  // Mixin Backbone.Events
  _.extend(Events.prototype, Backbone.Events);


  Events.prototype.eachEventHash_ = function(eventHash, callback, ctx) {
    ctx || (ctx = this);

    _.each(eventHash, function(handlerArr, topic) {
      // Normalize handler as array of handlers
      handlerArr = _.isArray(handlerArr) ? handlerArr : [handlerArr];

      // Bind each handler
      _.each(handlerArr, function(handler) {
        // Check if handle is a named method of this
        handler = _.isFunction(this[handler]) ? this[handler] : handler;

        // Call 'on' with standard signature
        callback.call(ctx, topic, handler);
      }, this);
    }, this);
  };


  /**
   * Bind an event handler to the object.
   * See Backbone.Events#on
   *
   * @method on
   */
  Events.prototype.on = function(events, ctx) {
    // Handle and normalize events hash
    if (_.isObject(events)) {
      this.eachEventHash_(events, function(topic, handler) {
        Backbone.Events.on.call(this, topic, handler, ctx);
      }, this);
    }
    // If we're not getting an events hash,
    // Just let Backbone.Events do its thing
    else {
      Backbone.Events.on.apply(this, arguments);
    }
  };


  /**
   * See http://backbonejs.org/#Events-off
   *
   * @method off
   */
  Events.prototype.off = function(event, handler, ctx) {
    // Handle and normalize events hash
    if (_.isObject(event)) {
      ctx = arguments[1];

      this.eachEventHash_(event, function(topic, handler) {
        Backbone.Events.off.call(this, topic, handler, ctx);
      }, this);
    }
    // If we're not getting an events hash,
    // Just let Backbone.Events do its thing
    else {
      Backbone.Events.off.apply(this, arguments);
    }
  };


  /**
   * Proxies all events from another {aeris.Event} object.
   * In other words, all the events that you trigger,
   * I'm gonna trigger too.
   *
   * Passes along the original object as the first argument
   * when triggering proxied events.
   *
   * @param {Events=} obj The object to proxy.
   * @param {function(string, Array):{Object}=} opt_callback
   *        A callback function to customize the proxied event.
   *        Should return on object with 'topic' and 'args' properties.
   *
   *        Example:
   *          parent.proxy(child, function(topic, args) {
   *            return {
   *              topic: 'child:' + topic,
   *              args: [child].concat(args)
   *            }
   *          });
   *
   *        ...would trigger all child events, with a topic prepended
   *        with 'child:', and with the child object inserted as the first
   *        argument.
   * @param {Object=} opt_ctx
   *        A context in which to call the opt_callback function.
   *        Defaults to this.
   * @method proxyEvents
   * @protected
   */
  Events.prototype.proxyEvents = function(obj, opt_callback, opt_ctx) {
    var trigger_orig = obj.trigger;
    var callback = opt_callback || function(topic, args) {
      return { topic: topic, args: args };
    };
    var ctx = opt_ctx || this;

    obj.trigger = function(topic, var_args) {
      // Process the callback
      // to get a new topic and arguments
      var args_orig = Array.prototype.slice.call(arguments, 1);
      var cbObj = callback.call(ctx, topic, args_orig);
      var args_proxy = [cbObj.topic].concat(cbObj.args);

      // Call the original object's trigger
      trigger_orig.apply(obj, arguments);

      // Call the proxying object's trigger
      this.trigger.apply(ctx, args_proxy);
    };
    obj.trigger = obj.trigger.bind(this);
  };


  /**
   * End any proxies that have been wrapped
   * around this {Events} object.
   * @method removeProxy
   * @protected
   */
  Events.prototype.removeProxy = function() {
    this.trigger = function() {
      Events.prototype.trigger.apply(this, arguments);
    };
    this.trigger = this.trigger.bind(this);
  };


  /**
   * A singleton instance of {aeris.Events}
   * @type {aeris.Events}
   */
  Events.hub = new Events();


  /**
   * Publish a global event
   * Same signature as {aeris.Events}#trigger
   *
   * @static
   * @method publish
   */
  Events.publish = function() {
    Events.hub.trigger.apply(Events.hub, arguments);
  };


  /**
   * Subscribe to a global event
   * Same signature as {aeris.Events}#on
   * @static
   * @method subscribe
   */
  Events.subscribe = function() {
    Events.hub.on.apply(Events.hub, arguments);
  };


  /**
   * Unsubscribe from a global event
   * Same signature as {aeris.Events}#off
   * @static
   * @method unsubscribe
   */
  Events.unsubscribe = function() {
    Events.hub.off.apply(Events.hub, arguments);
  };


  return _.expose(Events, 'aeris.Events');
});

define('aeris/errors/abstracterror',['aeris/util'], function(_) {
  /**
   * A custom Error.
   *
   * @abstract
   * @param {string} message Error message.
   * @constructor
   * @class aeris.errors.AbstractError
   */
  var AbstractError = function(message) {
    // See note above _.inherits.
    try {
      window.Error.call(this);
      Error.captureStackTrace(this, AbstractError);
    } catch (e) {}

    /**
     * The error's name. Should equal the class name, by convention.
     * @type {string}
     * @property name
     */
    this.name = this.setName();

    /**
     * The error message to throw.
     * @type {string}
     * @property message
     */
    this.message = this.setMessage.apply(this, arguments);
  };

  // Note: inheriting from the Error object may throw errors
  //  in some browsers (or so I'm told).
  //  This custom error will work either way, though
  //  with reduced functionality.
  try {
    _.inherits(
      AbstractError,
      window.Error
    );
  } catch (e) {}


  /**
   * Set the name of the error.
   *
   * @abstract
   * @return {string} Error name.
   * @method setName
   */
  AbstractError.prototype.setName = function() {
    // Cannot throw UnimplementedMethodError without a circular dependency
    // So we'll fake it
    throw 'UnimplementedMethodError: Classes extending from AbstractError must implement setName method';
  };


  /**
   * Set the error message
   *
   * @param {string} message Message passed into constructor.
   * @return {string} Error message.
   * @method setMessage
   */
  AbstractError.prototype.setMessage = function(message) {
    return message;
  };


  /**
   * Determines how error is displayed in (some) browser consoles
   *
   * @return {string}
   * @method toString
   */
  AbstractError.prototype.toString = function() {
    return this.name + ': ' + this.message;
  };


  return _.expose(AbstractError, 'aeris.errors.AbstractError');
});

define('aeris/errors/errortypefactory',[
  'aeris/util',
  'aeris/errors/abstracterror'
], function(_, AbstractError) {
  /**
   * Create custom Error classes on the fly.
   *
   * @class ErrorTypeFactory
   * @param {Object} config
   * @param {function():Error=} config.type Parent error type. Defaults to aeris.errors.AbstractError.
   * @param {string} config.name
   * @param {string=} config.message
   *
   * @return {function():Error} Error object constructor.
   * @constructor
   */
  var ErrorTypeFactory = function(config) {
    var ErrorType;

    _.defaults(config, {
      type: AbstractError
    });

    this.ParentType_ = config.type;

    ErrorType = this.initializeType_();


    ErrorType.prototype.setName = function() {
      return config.name;
    };

    ErrorType.prototype.setMessage = function() {
      var messageCb = config.message || function(msg) { return msg; };

      return messageCb.apply(this, arguments);
    };

    return ErrorType;
  };


  /**
   * @return {function():Error}
   * @private
   */
  ErrorTypeFactory.prototype.initializeType_ = function() {
    var ParentType = this.ParentType_;
    var Type = function(var_args) {
      ParentType.apply(this, arguments);
    };
    _.inherits(Type, ParentType);

    return Type;
  };


  return ErrorTypeFactory;
});

define('aeris/errors/validationerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.ValidationError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'ValidationError',
    message: function(attrName, opt_errorDetails) {
      return (opt_errorDetails) ?
        'Invalid model attribute \'' + attrName + '\': ' + opt_errorDetails + '.' :
        'Model failed to pass validation: ' + attrName;
    }
  });
});

define('aeris/model',[
  'aeris/util',
  'backbone',
  'aeris/events',
  'aeris/errors/validationerror'
], function(_, Backbone, Events, ValidationError) {
  /**
   * The base model class for Aeris JS Libraries
   *
   * See http://backbonejs.org/#Model for documentation
   *
   * @constructor
   *
   * @param {Object=} opt_attrs
   *
   * @param {Object=} opt_options
   * @param {Boolean=} opt_options.validate
   *        If set to true, model will immediately check validation
   *        on instantiation.
   *
   * @class aeris.Model
   * @extends Backbone.Model
   * @uses aeris.Events
   */
  var Model = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      validate: false
    });

    // We don't want ctor to validate before we
    // have bound validation error handlers
    var parentCtorOptions = _.omit(options, 'validate');


    /**
     * @property idAttribute
     */
    this.idAttribute = options.idAttribute || 'id';


    /**
     * @property defaults
     */
    this.defaults = this.defaults || options.defaults;


    /**
     * The options used when constructing
     * the model.
     *
     * @type {Object}
     * @private
     * @property options_
     */
    this.options_ = opt_options || {};


    Backbone.Model.call(this, opt_attrs, parentCtorOptions);
    Events.call(this);

    // Handle validation errors
    this.on('invalid', this.onValidationError_, this);

    if (options.validate) {
      this.isValid();
    }


    /**
     * When a model's attribute changes
     * @event change
     * @param {aeris.Model} model
     */

    /**
     * When a model's attribute changes,
     * where [attribute] is the name of the attribute.
     *
     * @event change:[attribute]
     * @param {aeris.Model} model
     * @param {*} value
     */

    /**
     * When a model is added to a
     * {aeris.Collection}.
     *
     * @event add
     * @param {aeris.Model} model
     * @param {aeris.Collection} collection
     */

    /**
     * When a model is removed from a {aeris.Collection}.
     *
     * @event remove
     * @param {aeris.Model} model
     * @param {aeris.Collection} collection
     */

  };
  _.inherits(Model, Backbone.Model);
  _.extend(Model.prototype, Events.prototype);


  /**
   * Handle 'invalid' events thrown by the model.
   *
   * @param {aeris.Model} model
   * @param {aeris.errors.ValidationError} error
   * @private
   * @method onValidationError_
   */
  Model.prototype.onValidationError_ = function(model, error) {
    error = (error instanceof Error) ? error : new ValidationError(error);
    throw error;
  };

  /**
   * Validate the model's attributes.
   *
   * @override
   * @method isValid
   * @throws {aeris.errors.ValidationError}
   */


  /**
   * Normalize attributes before setting
   *
   * @param {Object} config
   * @protected
   * @method set
   */
  Model.prototype.set = function(key, value, opts) {
    var config;

    // Convert args to { key: value } format
    if (_.isString(key)) {
      (config = {})[key] = value;
    }
    else {
      config = key;

      // Options are the second argument
      opts = value;
    }

    // Normalize config before setting
    config = this.normalize_(config);

    if (!config) {
      throw Error('Invalid model attributes. ' +
        'Make sure that Model#normalize_ is returning an object.');
    }

    return Backbone.Model.prototype.set.call(this, config, opts);
  };


  /**
   * This method is called every time attributes
   * are set on the model.
   *
   * Override to provide any additional processing
   * needed for options object structure, etc.
   *
   * @param {Object} attrs
   * @return {attrs} Normalized attrs.
   *
   * @protected
   * @method normalize_
   */
  Model.prototype.normalize_ = function(attrs) {
    return attrs;
  };


  /**
   * Returns a deep-nested property
   * of a model attribute.
   *
   * Example:
   *    model.set('deepObj', {
   *      levelA: {
   *        levelB: {
   *          foo: 'bar'
   *        }
   *      }
   *    });
   *
   *    model.getAtPath('deepObj.levelA.levelB.foo');      // 'bar'
   *
   * Returns undefined if the path cannot be resolved.
   *
   * @param {string} path
   * @return {*|undefined}
   * @method getAtPath
   */
  Model.prototype.getAtPath = function(path) {
    var pathParts = path.split('.');
    var attrName = pathParts.splice(0, 1)[0];
    var attrObj = this.get(attrName);

    return pathParts.length ? _.path(pathParts.join('.'), attrObj) : attrObj;
  };


  /**
   * Create a copy of the model.
   *
   * @method clone
   * @param {Object=} opt_attrs Attributes to set on the cloned model.
   * @param {Object=} opt_options Options to pass to cloned mode.
   * @return {aeris.Model}
   */
  Model.prototype.clone = function(opt_attrs, opt_options) {
    var attributes = _.extend({}, this.attributes, opt_attrs);
    var options = _.extend({}, this.options_, opt_options);

    return new this.constructor(attributes, options);
  };


  /**
   * Keep this model updated with values
   * from the target model.
   *
   * Immediately updates the model with the specified
   * attributes, and updates this model whenever the
   * target model's attributes change.
   *
   * @method bindAttributesTo
   * @param {aeris.Model} target Model to bind to.
   * @param {Array.<string>} attrs Attributes to bind.
   */
  Model.prototype.bindAttributesTo = function(target, attrs) {
    var update = this.updateWithAttributesOf_.bind(this, target, attrs);
    var attrEvents = attrs.map(function(attr) {
      return 'change:' + attr;
    });

    // Sync to target immediately
    update();

    // Sync to changes in target
    this.listenTo(target, attrEvents.join(' '), update);
  };


  /**
   * Update the attributes of the model
   * with attributes from another model.
   *
   * @private
   *
   * @method updateWithAttributesOf_
   * @param {aeris.Model} target Source model.
   * @param {Array.<string>} attrs List of attributes to update.
   */
  Model.prototype.updateWithAttributesOf_ = function(target, attrs) {
    // Create { attrName: targetValue } hash
    // for all attributes
    var attrValues = attrs.reduce(function(obj, attr) {
      obj[attr] = target.get(attr);
      return obj;
    }, {});

    this.set(attrValues, { validate: true });
  };


  return Model;
});
/**
 * See http://backbonejs.org/ for full documentation.
 * Included here to provide documentation for
 * inherited aeris.Model classes
 *
 * @class Backbone.Model
 *
 * @param {Object=} opt_attrs Attributes to set on the model.
 *
 * @param {Object=} opt_options
 * @param {Backbone.Collection=} opt_options.collection
 * @param {Boolean=} opt_options.parse
 */

/**
 * @method get
 * @protected
 *
 * @param {string} attribute
 * @return {*}
 */

/**
 * @method set
 * @protected
 *
 * @param {Object|string} attributes
 * @param {Object|*=} opt_options
 * @param {Boolean=} opt_options
 */

/**
 * @protected
 * @method has
 * @return {Boolean}
 */

/**
 * @param {string} attribute
 * @protected
 * @method unset
 */

/**
 * @protected
 * @method
 */

/**
 * @protected
 * @property idAttribute
 * @type {string}
 */

/**
 * @protected
 * @property id
 * @type {number|string}
 */

/**
 * @protected
 * @property cid
 * @type {number|string}
 */

/**
 * @protected
 * @property attributes
 * @type {Object}
 */

/**
 * @protected
 * @property defaults
 * @type {Object}
 */

/**
 * @method toJSON
 * @return {Object} A shallow copy of the model's attributes.
 */

/**
 * @protected
 * @method sync
 * @param {string} method
 * @param {Backbone.Model} model
 * @param {Object=} options
 */

/**
 * Fetch model data.
 *
 * @method fetch
 */

/**
 * @method validate
 * @protected
 */

/**
 * @method parse
 * @protected
 */

/**
 * @method sync
 * @protected
 */
;
define('aeris/errors/invalidargumenterror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.InvalidArgumentError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'InvalidArgumentError'
  });
});

define('aeris/viewmodel',[
  'aeris/util',
  'aeris/model',
  'aeris/errors/invalidargumenterror'
], function(_, Model, InvalidArgumentError) {
  /**
   * A representation of a data model, which has been
   * reshaped into a form expected by a view.
   *
   * Inspired by https://github.com/tommyh/backbone-view-model
   *
   * @class aeris.ViewModel
   * @extends aeris.Model
   *
   * @constructor
   * @override
   *
   * @param {Object=} opt_attrs
   * @param {Object=} opt_options
   *
   * @param {Backbone.Model=} opt_options.data Data model.
   */
  var ViewModel = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      data: new Model(),
      attributeTransforms: this.attributeTransforms
    });


    /**
     * Data model.
     *
     * @type {aeris.Model}
     * @private
     * @property data_
     */
    this.data_ = options.data;


    /**
     * A hash of transforms to apply
     * to attributes.
     *
     * Example:
     *  {
     *    km: function() {
     *      return this.getData().get('miles') * 1.609344;
     *    }
     *  }
     *
     * this.getData().set('miles', 3.0);
     * this.get('km');     // 4.82803
     *
     *
     * @type {Object.<string,Function>}
     * @private
     * @property attributeTransforms_
     */
    this.attributeTransforms_ = options.attributeTransforms;


    Model.call(this, opt_attrs, options);


    this.listenTo(this.data_, {
      change: this.syncToModel
    });
    this.syncToModel();
  };
  _.inherits(ViewModel, Model);


  /**
   * Uses attribute transforms to sync
   * the view model to the data model.
   * @method syncToModel
   */
  ViewModel.prototype.syncToModel = function() {
    var attrs = {};

    _.each(this.attributeTransforms_, function(transform, key) {
      attrs[key] = transform.call(this);
    }, this);

    this.set(attrs, { validate: true });
  };


  /**
   * @return {aeris.Model} The data model associated with this view model.
   * @method getData
   */
  ViewModel.prototype.getData = function(opt_path) {
    if (opt_path) {
      return this.getDataAttribute(opt_path);
    }
    return this.data_;
  };


  /**
   * Returns an attribute of the data
   * model at a given path.
   *
   * @param {string} path
   * @return {string}
   * @method getDataAttribute
   */
  ViewModel.prototype.getDataAttribute = function(path) {
    var pathParts, baseDataAttr, nestedAttrsPath;

    if (_.isUndefined(path)) {
      throw new InvalidArgumentError('Unable to get data attribute ' + path);
    }

    pathParts = path.split('.');
    baseDataAttr = this.data_.get(pathParts[0]);
    nestedAttrsPath = pathParts.slice(1).join('.');

    return nestedAttrsPath ? _.path(nestedAttrsPath, baseDataAttr) : baseDataAttr;
  };


  /**
   * Invoke the 'fetch' method on our
   * data model.
   *
   * @param {Object=} opt_options Options to pass to the aeris.Model#fetch method.
   * @return {aeris.Promise}
   * @method fetchData
   */
  ViewModel.prototype.fetchData = function(opt_options) {
    return this.data_.fetch(opt_options);
  };


  /**
   * @override
   * Shuts down model-viewModel data bindings.
   * @method destroy
   */
  ViewModel.prototype.destroy = function() {
    this.stopListening(this.data_);
  };


  return ViewModel;
});

define('aeris/promise',[
  'aeris/util',
  'aeris/errors/invalidargumenterror'
], function(_, InvalidArgumentError) {


  /**
   * Create a lightweight Promise for async related work.
   *
   * @constructor
   * @publicApi
   * @class aeris.Promise
   */
  var Promise = function() {


    /**
     * The current state of the promise (e.g. pending, resolved, or rejected).
     *
     * @type {string}
     * @property state
     */
    this.state = 'pending';


    /**
     * An array of arguments to send to the callbacks.
     *
     * @type {Array}
     * @private
     * @property arguments_
     */
    this.arguments_ = null;


    /**
     * @typedef {Array.<Array.<Function, Object>> callbackStore
     */
    /**
     * An object containing deferred callbacks for
     * resolved and rejected states.
     *
     * @type {{done: {callbackStore}, fail: {callbackStore}, always: {callbackStore}}}
     * @private
     * @property deferred_
     */
    this.deferred_ = {
      resolved: [],
      rejected: []
    };

    // Bind resolve/reject to the promise instance
    this.resolve = this.resolve.bind(this);
    this.reject = this.reject.bind(this);
  };


  /**
   * Ensure a callback is called when the promise is resolved.
   *
   * @param {Function} callback
   * @param {Object} opt_ctx Callback context.
   * @method done
   */
  Promise.prototype.done = function(callback, opt_ctx) {
    this.bindCallbackToState_('resolved', callback, opt_ctx);

    return this;
  };


  /**
   * Ensure a callback is called when the promise is rejected.
   *
   * @param {Function} callback
   * @param {Object} opt_ctx Callback context.
   * @method fail
   */
  Promise.prototype.fail = function(callback, opt_ctx) {
    this.bindCallbackToState_('rejected', callback, opt_ctx);

    return this;
  };


  /**
   * Ensure a callback is called when the promise is either resolved or rejected.
   *
   * @param {Function} callback
   * @param {Object} opt_ctx Callback context.
   * @method always
   */
  Promise.prototype.always = function(callback, opt_ctx) {
    this.done(callback, opt_ctx);
    this.fail(callback, opt_ctx);

    return this;
  };


  /**
   * Ensure a callback is called when the promise adopts the specified state.
   *
   * @throws {aeris.errors.InvalidArgumentError} If no callback defined.
   *
   * @param {'resolved'|'rejected'} state
   * @param {Function} callback
   * @param {Object} opt_ctx Callback context.
   * @method bindCallbackToState_
   * @private
   */
  Promise.prototype.bindCallbackToState_ = function(state, callback, opt_ctx) {
    if (!_.isFunction(callback)) {
      throw new InvalidArgumentError('Invalid \'' + state + '\' state callback.');
    }

    // If state is already bound, immediately invoke callback
    if (this.state === state) {
      callback.apply(opt_ctx, this.arguments_);
    }
    else {
      this.deferred_[state].push([callback, opt_ctx]);
    }
  };


  /**
   * Mark a promise is resolved, passing in a variable number of arguments.
   *
   * @param {...*} var_args A variable number of arguments to pass to callbacks.
   * @method resolve
   */
  Promise.prototype.resolve = function(var_args) {
    this.adoptState_('resolved', arguments);
  };


  /**
   * Mark a promise is rejected, passing in a variable number of arguments.
   *
   * @param {...*} var_args
   * @method reject
   */
  Promise.prototype.reject = function(var_args) {
    this.adoptState_('rejected', arguments);
  };


  /**
   * Mark a promise with the specified state
   * passing in an array of arguments
   *
   * @param {'resolved'|'rejected'} state The state with which to mark the promise.
   * @param {Array} opt_args An array of responses to send to deferred callbacks.
   * @private
   * @method adoptState_
   */
  Promise.prototype.adoptState_ = function(state, opt_args) {
    var length;
    var callbacks;

    // Enforce state is 'rejected' or 'resolved'
    if (state !== 'rejected' && state !== 'resolved') {
      throw new Error('Invalid promise state: \'' + state + '\'. +' +
        'Valid states are \'resolved\' and \'rejected\'');
    }

    if (this.state === 'pending') {
      this.state = state;
      this.arguments_ = opt_args;

      // Run all callbacks
      callbacks = this.deferred_[this.state];
      length = callbacks.length;
      for (var i = 0; i < length; i++) {
        var fn = callbacks[i][0];
        var ctx = callbacks[i][1];
        fn.apply(ctx, this.arguments_);
      }

      // Cleanup callbacks
      for (var cbState in this.deferred_) {
        if (this.deferred_.hasOwnProperty(cbState)) {
          this.deferred_[cbState] = [];
        }
      }
    }
    // Do nothing if promise is already resolved/rejected
  };


  /**
   *
   * @return {string} The current state of the promise.
   *  'pending', 'resolved', or 'rejected'.
   * @method getState
   */
  Promise.prototype.getState = function() {
    return this.state;
  };


  /**
   * Resolve/reject the promise
   * when the proxy promise is resolved/rejected.
   *
   * @method {aeris.Promise} proxy
   */
  Promise.prototype.proxy = function(proxyPromise) {
    proxyPromise.
      done(this.resolve).
      fail(this.reject);

    return this;
  };


  /**
   * Create a master promise from a combination of promises.
   * Master promise is resolved when all component promises are resolved,
   * or rejected when any single component promise is rejected.
   *
   * @param {...*} var_args A variable number of promises to wait for or an.
   *                        array of promises.
   * @return {aeris.Promise} Master promise.
   * @method when
   */
  Promise.when = function(var_args) {
    var promises = Array.prototype.slice.call(arguments);
    var masterPromise = new Promise();
    var masterResponse = [];
    var resolvedCount = 0;

    // Allow first argument to be array of promises
    if (promises.length === 1 && promises[0] instanceof Array) {
      promises = promises[0];
    }

    promises.forEach(function(promise, i) {
      if (!(promise instanceof Promise)) {
        throw new InvalidArgumentError('Unable to create master promise: ' +
          promise.toString() + ' is not a valid Promise object');
      }

      promise.fail(function() {
        masterPromise.reject.apply(masterPromise, arguments);
      });

      promise.done(function() {
        var childResponse = _.argsToArray(arguments);
        masterResponse[i] = childResponse;
        resolvedCount++;

        if (resolvedCount === promises.length) {
          masterPromise.resolve.apply(masterPromise, masterResponse);
        }
      });
    }, this);

    // Resolve immediately if called with no promises
    if (promises.length === 0) {
      masterPromise.resolve();
    }

    return masterPromise;
  };


  /**
   * Calls the promiseFn with each member in `objects`.
   * Each call to the promiseFn will be postponed until the promise
   * returned by the previous call is resolved.
   *
   * @param {Array<*>} objects
   * @param {function():aeris.Promise} promiseFn
   * @return {aeris.Promise}
   *         Resolves with an array containing the resolution value of each
   *         call to the promiseFn.
   */
  Promise.sequence = function(objects, promiseFn) {
    var promiseToResolveAll = new Promise();
    var resolvedArgs = [];
    var rejectSequence = promiseToResolveAll.reject.
      bind(promiseToResolveAll);
    var resolveSequence = promiseToResolveAll.resolve.
      bind(promiseToResolveAll, resolvedArgs);

    var nextAt = function(i) {
      var next = _.partial(nextAt, i + 1);
      var obj = objects[i];

      if (obj) {
        Promise.callPromiseFn_(promiseFn, obj).
          done(function(arg) {
            // When the promiseFn resolves,
            // Save the resolution data
            // and run again with the next object.
            resolvedArgs.push(arg);
            next();
          }).
          fail(rejectSequence);
      }
      else {
        // No more objects exist,
        // --> we're done.
        resolveSequence();
      }
    };
    nextAt(0);

    return promiseToResolveAll;
  };


  /**
   *
   * @param {function():Promise} promiseFn
   * @param {*...} var_args
   * @private
   */
  Promise.callPromiseFn_ = function(promiseFn, var_args) {
    var args = Array.prototype.slice.call(arguments, 1);
    var promise = promiseFn.apply(null, args);

    if (!(promise instanceof Promise)) {
      throw new InvalidArgumentError('Promise.sequence expects the promiseFn ' +
        'argument to return an aeris.Promise object.');
    }

    return promise;
  };


  /**
   * Similar to Promise#when, but accepts a map function
   * which transforms array members into promises.
   *
   * eg.
   *
   *  var apiEndpoints = [ '/endpointA', '/endpointB' ];
   *
   *  function request(endpoint) {
   *  // .. returns a promise
   *  }
   *
   *  // Resolves when requests have completed for all endpoints.
   *  Promise.map(apiEndpoints, request);
   *
   * @param {Array} arr
   * @param {function(*):aeris.Promise} mapFn
   * @return {aeris.Promise}
   */
  Promise.map = function(arr, mapFn, opt_ctx) {
    return Promise.when(arr.map(mapFn, opt_ctx));
  };

  Promise.resolve = function(val) {
    var promise = new Promise();
    promise.resolve(val);
    return promise;
  };


  return _.expose(Promise, 'aeris.Promise');

});

define('aeris/maps/extensions/strategyobject',[
  'aeris/util',
  'aeris/events',
  'aeris/promise',
  'aeris/errors/invalidargumenterror'
], function(_, Events, Promise, InvalidArgumentError) {
  /**
   * An object bound to a strategy.
   *
   * @class aeris.maps.extensions.StrategyObject
   * @constructor
   *
   * @param {Object=} opt_options
   * @param {function():aeris.maps.AbstractStrategy} opt_options.strategy
   *        The constructor for a {aeris.maps.AbstractStrategy} object.
   */
  var StrategyObject = function(opt_options) {
    var options = _.defaults(opt_options || {}, {
      strategy: null
    });

    /**
     * The strategy used to interact
     * with the map view.
     *
     * @property strategy_
     * @type {?aeris.maps.AbstractStrategy}
     * @protected
     */
    this.strategy_ = null;

    /**
     * @property StrategyType_
     * @private
     * @type {function():aeris.maps.AbstractStrategy}
     */
    this.StrategyType_ = options.strategy;

    Events.call(this);


    if (!_.isNull(this.StrategyType_)) {
      this.setStrategy(this.StrategyType_);
    }


    /**
     * When a strategy is set on the object.
     *
     * @event strategy:set
     * @param {aeris.maps.AbstractStrategy} strategy
     */
  };
  _.extend(StrategyObject.prototype, Events.prototype);


  /**
   * Set the strategy to use for
   * rendering the StrategyObject.
   *
   * @param {Function} Strategy
   *        Constructor for an {aeris.maps.AbstractStrategy} object.
   * @method setStrategy
   */
  StrategyObject.prototype.setStrategy = function(Strategy) {
    // Clean up any existing strategy
    if (this.strategy_) {
      this.removeStrategy();
    }

    if (!_.isFunction(Strategy)) {
      throw new InvalidArgumentError('Unable to set StrategyObject strategy: ' +
        'invalid strategy constructor.');
    }

    this.StrategyType_ = Strategy;
    this.strategy_ = this.createStrategy_(Strategy);

    this.trigger('strategy:set', this.strategy_);
  };


  /**
   * Create a {aeris.maps.AbstractStrategy} instance.
   *
   * Override to adjust how strategy objects are
   * instantiated.
   *
   * @protected
   *
   * @param {Function} Strategy AbstractStrategy object ctor.
   * @return {aeris.maps.AbstractStrategy}
   * @method createStrategy_
   */
  StrategyObject.prototype.createStrategy_ = function(Strategy) {
    return new Strategy(this);
  };


  /**
   * Remove and clean up the StrategyObject's strategy.
   * @method removeStrategy
   */
  StrategyObject.prototype.removeStrategy = function() {
    if (!this.strategy_) {
      return;
    }

    this.strategy_.destroy();
    this.strategy_ = null;
  };


  /**
   * Reset the rendering strategy used by the
   * object. Useful for re-enabled a strategy which has
   * previously been removed with StrategyObject#removeStrategy
   *
   * @method resetStrategy
   */
  StrategyObject.prototype.resetStrategy = function() {
    if (!this.StrategyType_) {
      throw new Error('Unable to reset strategy: no strategy has yet been defined');
    }

    this.setStrategy(this.StrategyType_);
  };


  return StrategyObject;
});

define('aeris/maps/extensions/mapextensionobject',[
  'aeris/util',
  'aeris/errors/validationerror',
  'aeris/viewmodel',
  'aeris/maps/extensions/strategyobject',
  'aeris/promise'
], function(_, ValidationError, ViewModel, StrategyObject, Promise) {
  /**
   * An abstraction for an object to be handled by a map extension.
   *
   * A MapExtensionObject holds meta-data about view being rendered
   * on a map; for example, the color of a polyline, the opacity of
   * a layer, the url endpoint for fetching tile images.
   *
   * A MapExtensionObject creates a strategy, whose job is to render
   * the metadata held by a MapExtObj onto a map. The MapExtObj tells
   * the strategy about itself, then let's the strategy do it's thing.
   *
   * @override
   * @param {Object=} opt_attrs
   * @param {Object=} opt_options
   *
   * @constructor
   *
   * @class aeris.maps.extensions.MapExtensionObject
   * @extends aeris.ViewModel
   * @uses aeris.maps.extensions.StrategyObject
   * @implements aeris.maps.MapObjectInterface
   */
  var MapExtensionObject = function(opt_attrs, opt_options) {
    var attrs = _.defaults(opt_attrs || {}, {
      map: null
    });


    /**
     * Default {aeris.Strategy} implementation
     *
     * @property strategy_
     * @type {aeris.maps.Strategy} Strategy constructor.
     */


    /**
     * An AerisMap that the object is bound to. This is set with setMap.
     *
     * @attribute map
     * @type {aeris.maps.Map}
     * @protected
     */


    /**
     * A name/type for the object.
     *
     * @attribute name
     * @type {string}
     */

    ViewModel.call(this, attrs, opt_options);

    StrategyObject.call(this, _.pick(opt_options || {}, 'strategy'));


    // Trigger map:set/remove events
    this.listenTo(this, {
      'change:map': function(model, value, options) {
        var topic = this.hasMap() ? 'map:set' : 'map:remove';
        this.trigger(topic, model, value, options);
      }
    });
  };
  _.inherits(MapExtensionObject, ViewModel);
  _.extend(MapExtensionObject.prototype, StrategyObject.prototype);


  /**
   * @method validate
   */
  MapExtensionObject.prototype.validate = function(attrs) {
    if (attrs.map !== null && !(attrs.map instanceof aeris.maps.Map)) {
      return new ValidationError('Aeris Map', 'Invalid map object');
    }
  };


  /**
   * @method setMap
   */
  MapExtensionObject.prototype.setMap = function(aerisMap, opt_options) {
    var options = _.defaults(opt_options || {}, {
      validate: true
    });
    this.set('map', aerisMap, options);
  };

  /**
   * @method getMap
   */
  MapExtensionObject.prototype.getMap = function() {
    return this.get('map');
  };


  /**
   * @return {Boolean} Returns true if the layer has a map set.
   * @method hasMap
   */
  MapExtensionObject.prototype.hasMap = function() {
    return this.has('map');
  };


  /**
   * Returns the object view,
   * as rendered by the object's strategy.
   *
   * @throws {Error} If no strategy has been set on the object.
   *
   * @return {*}
   * @method getView
   */
  MapExtensionObject.prototype.getView = function() {
    if (!this.strategy_) {
      throw new Error('Unable to get MapExtensionObject view: ' +
        'no strategy is available for this object.');
    }

    return this.strategy_.getView();
  };


  /**
   * @method destroy
   */
  MapExtensionObject.prototype.destroy = function() {
    this.stopListening();
    this.removeStrategy();
  };


  return MapExtensionObject;

});

define('aeris/maps/abstractstrategy',[
  'aeris/util',
  'aeris/events'
], function(_, Events) {
  /**
   * A Strategy is created by a MapExtensionObject. It's job is
   * to:
   * - Render the MapExtObj
   * - Listen for changes to a MapExtObj, and render
   *   those changes.
   *
   * Strategies may use mapping libraries (eg. gmaps/openlayers)
   * to render a MapExtObj. In fact, strategies should be the only
   * place in the library where we find direct interactions with
   * specific mapping libraries.
   *
   * @class aeris.maps.AbstractStrategy
   * @constructor
   *
   * @param {aeris.maps.MapObjectInterface} obj
   *        The aeris object to associate with the map view.
   */
  var AbstractStrategy = function(obj) {
    /**
     * @type {aeris.maps.extensions.MapExtensionObject}
     * @protected
     * @property object_
     */
    this.object_ = obj;


    /**
     * The map associated with this object
     * @type {?google.maps.Map}
     * @property mapView_
     */
    this.mapView_;

    /**
     * Evens to bind the map view to the
     * object.
     *
     * Binds object attribute 'change'
     * events to strategy methods.
     *
     * @private
     * @property objectEvents_
     */
    this.objectEvents_ = _.extend({}, this.objectEvents_, {
      'map:set': function(model, map) {
        this.setMap(map);
      },
      'map:remove': this.remove
    });


    Events.call(this);

    /**
     * The view instance created by
     * the map rendering API.
     *
     * @type {Object}
     * @property view_
     */
    this.view_ = this.createView_();


    // Bind this.objectEvents to this.object_
    this.listenTo(this.object_, this.objectEvents_);


    // Set to map, if object has one
    if (this.object_.hasMap()) {
      this.setMap(this.object_.getMap());
    }
  };
  _.extend(AbstractStrategy.prototype, Events.prototype);


  /**
   * Render an object on a map.
   *
   * @param {aeris.maps.Map} aerisMap
   * @abstract
   * @method setMap
   */
  AbstractStrategy.prototype.setMap = function(aerisMap) {
    // Remove the object first, if it's already
    // set to a map
    if (this.mapView_) { this.remove(); }

    // Store a reference to the map view
    this.mapView_ = aerisMap.getView();

    // Child class should do something
    // useful here...
  };


  /**
   * Remove the object view from the map view.
   * @method remove
   */
  AbstractStrategy.prototype.remove = function() {
    // If no map exists, nothing to do here.
    if (!this.mapView_) { return; }


    // Child class should do something
    // useful in beforeRemove_
    this.beforeRemove_();


    // Remove reference to mapview
    this.mapView_ = null;

    this.afterRemove_();
  };


  /**
   * Destroy the rendered map object view, and cease
   * rendering changes to the map object.
   *
   * @method destroy
   */
  AbstractStrategy.prototype.destroy = function() {
    this.stopListening();
    this.remove();
  };



  /**
   * This method is called before
   * our reference to this.mapView_ is set
   * to null.
   *
   * This method must be overridden
   * to do the actual work of un-rendering
   * the map object.
   *
   * @method
   * @protected
   * @abstract
   * @method beforeRemove_
   */
  AbstractStrategy.prototype.beforeRemove_ = _.abstractMethod;


  /**
   * This method is called after
   * this.mapView_ is set to null;
   *
   * @method
   * @protected
   * @method afterRemove_
   */
  AbstractStrategy.prototype.afterRemove_ = function() {};


  /**
   * Create a view instance.
   *
   * @protected
   * @abstract
   * @return {Object} View instance.
   * @method createView_
   */
  AbstractStrategy.prototype.createView_ = _.abstractMethod;


  /**
   * Return the view instance
   * created by the map-rendering API.
   *
   * @return {?Object}
   * @method getView
   */
  AbstractStrategy.prototype.getView = function() {
    return this.view_;
  };


  return AbstractStrategy;
});

define('aeris/errors/unimplementedpropertyerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.UnimplementedPropertyError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'UnimplementedPropertyError',
    message: function(message) {
      return message ?
        'Abstract property ' + message + ' has not been implemented' :
        'Abstract property has not been implemented';
    }
  });
});

define('aeris/maps/layers/errors/layerloadingerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.maps.layers.errors.LayerLoadingError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'LayerLoadingError'
  });
});

define('aeris/maps/layers/layer',[
  'aeris/util',
  'aeris/maps/extensions/mapextensionobject'
], function(_, MapExtensionObject) {
  /**
   * Base class for all layers.
   *
   * @constructor
   * @class aeris.maps.layers.Layer
   *
   * @extends aeris.maps.extensions.MapExtensionObject
   */
  var Layer = function() {
    MapExtensionObject.apply(this, arguments);
  };
  _.inherits(Layer, MapExtensionObject);


  return Layer;
});

define('aeris/maps/layers/animationlayer',[
  'aeris/util',
  'aeris/maps/layers/layer'
], function(_, BaseLayer) {
  /**
   * An animation layer is a layer
   * which can be animated.
   *
   * @constructor
   * @class aeris.maps.layers.AnimationLayer
   * @extends aeris.maps.layers.Layer
   */
  var AnimationLayer = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      /**
       * Milliseconds between an autoupdate of the data.
       *
       * @attribute autoUpdateInterval
       * @type {number}
       * @default 6000 (1 second)
       */
      autoUpdateInterval: 1000 * 60
    }, opt_attrs);


    BaseLayer.call(this, attrs, opt_options);
  };

  _.inherits(AnimationLayer, BaseLayer);


  /**
   * Experimental layer fade animation.
   *
   * @param {number} targetOpacity
   * @param {number} duration In milliseconds.
   * @method fadeTo
   */
  AnimationLayer.prototype.fadeTo = function(targetOpacity, duration) {
    var direction = this.get('opacity') > targetOpacity ? -1 : 1;
    var spread = Math.abs(this.get('opacity') - targetOpacity);
    var interval = 13;
    var framesCount = duration / interval;
    var delta = spread / framesCount;

    this.stop();

    this.animationClock_ = window.setInterval(
      _.bind(function() {
        var nextOpacity = this.get('opacity') + (delta * direction);

        if (nextOpacity < 0 || nextOpacity > 1) {
          this.setOpacity(targetOpacity);
          window.clearInterval(this.animationClock_);
          return;
        }
        this.setOpacity(nextOpacity);

        if (direction === -1 && this.get('opacity') <= targetOpacity ||
          direction === 1 && this.get('opacity') >= targetOpacity
        ) {
          window.clearInterval(this.animationClock_);
        }
      }, this),
      interval);
  };


  AnimationLayer.prototype.fadeOut = function(duration) {
    this.fadeTo(0, duration);
  };

  AnimationLayer.prototype.fadeIn = function(duration) {
    this.fadeTo(1, duration);
  };

  AnimationLayer.prototype.stop = function() {
    if (this.animationClock_) {
      window.clearInterval(this.animationClock_);
    }

    return this;
  };


  /**
   * Show the layer.
   * @method show
   */
  AnimationLayer.prototype.show = function() {
    this.setOpacity(1.0);
  };


  /**
   * Hide the layer.
   * @method hide
   */
  AnimationLayer.prototype.hide = function() {
    this.setOpacity(0);
  };

  return AnimationLayer;
});

define('leaflet',[],function() {
  return window.L;
});

define('aeris/maps/strategy/layers/tile',[
  'aeris/util',
  'aeris/maps/abstractstrategy',
  'leaflet'
], function(_, AbstractStrategy, Leaflet) {
  /**
   * Strategy for rendering a tile layer
   * using Leaflet.
   *
   * @class aeris.maps.leaflet.layers.Tile
   * @extends aeris.maps.strategy.AbstractStrategy
   *
   * @constructor
   */
  var Tile = function(mapObject) {
    AbstractStrategy.call(this, mapObject);

    this.proxyLoadEvents_();
    this.bindLayerAttributes_();
  };
  _.inherits(Tile, AbstractStrategy);


  /**
   * @method createView_
   * @private
   */
  Tile.prototype.createView_ = function() {
    var tileLayer = new Leaflet.TileLayer(this.getTileUrl_(), {
      subdomains: this.object_.get('subdomains'),
      minZoom: this.object_.get('minZoom'),
      maxZoom: this.object_.get('maxZoom'),
      opacity: this.object_.get('opacity'),
      zIndex: this.object_.get('zIndex'),
      attribution: this.getOSMAttribution_()
    });

    return tileLayer;
  };


  /**
   * @method setMap
   */
  Tile.prototype.setMap = function(map) {
    AbstractStrategy.prototype.setMap.call(this, map);

    this.view_.addTo(map.getView());
  };


  /**
   * @method beforeRemove
   */
  Tile.prototype.beforeRemove_ = function() {
    this.mapView_.removeLayer(this.view_);
  };


  /**
   * Returns the tile layer url pattern,
   * formatted for Leaflet.
   *
   * @method getTileUrl_
   * @private
   * @return {string}
   */
  Tile.prototype.getTileUrl_ = function() {
    return this.object_.getUrl().
      replace('{d}', '{s}');
  };


  /**
   * @method getOSMAttribution_
   * @private
   * @return {string}
   */
  Tile.prototype.getOSMAttribution_ = function() {
    return '<a href="https://www.openstreetmap.org/copyright" target="_blank">' +
      '&copy OpenStreetMap contributors' +
      '</a>';
  };


  /**
   * @method proxyLoadEvents_
   * @private
   */
  Tile.prototype.proxyLoadEvents_ = function() {
    var triggerLoadReset = this.object_.trigger.bind(this.object_, 'load:reset');
    var loadResetEventsHash = {
      loading: triggerLoadReset
    };

    // Tiles loading is reset whenever map moves
    var bindMapLoadResetEvents = (function() {
      var mapView = this.mapView_;

      this.mapView_.addEventListener(loadResetEventsHash, this);

      this.once('map:remove', function() {
        mapView.removeEventListener(loadResetEventsHash, this);
      });
    }).bind(this);


    this.view_.addEventListener({
      load: function() {
        this.object_.trigger('load');
      }
    }, this);

    this.listenTo(this.object_, {
      'map:set': bindMapLoadResetEvents
    });

    if (this.object_.hasMap()) {
      bindMapLoadResetEvents();
    }
  };


  /**
   * @method bindLayerAttributes_
   * @private
   */
  Tile.prototype.bindLayerAttributes_ = function() {
    this.listenTo(this.object_, {
      'change:opacity': function() {
        this.view_.setOpacity(this.object_.get('opacity'));
      },
      'change:zIndex': function() {
        this.view_.setZIndex(this.object_.get('zIndex'));
      }
    });
  };


  /**
   * @method destroy
   */
  Tile.prototype.destroy = function() {
    this.view_.clearAllEventListeners();
    AbstractStrategy.prototype.destroy.call(this);
    this.view_ = null;
  };


  return Tile;
});

define('aeris/maps/layers/abstracttile',[
  'aeris/util',
  'aeris/promise',
  'aeris/events',
  'aeris/errors/unimplementedpropertyerror',
  'aeris/errors/validationerror',
  'aeris/maps/layers/errors/layerloadingerror',
  'aeris/maps/layers/animationlayer',
  'aeris/maps/strategy/layers/tile'
], function(_, Promise, Events, UnimplementedPropertyError, ValidationError, LayerLoadingError, AnimationLayer, TileStrategy) {
  /**
   * Representation of image tile layer. Tile layers are
   * expected to pull in tile images from an API.
   *
   *
   * @constructor
   * @class aeris.maps.layers.AbstractTile
   * @extends aeris.maps.layers.AnimationLayer
   */
  var AbstractTile = function(opt_attrs, opt_options) {
    /**
     * Fires when tile images are loaded.
     *
     * @event load
     */
    /**
     * Firest when tile images must
     * be re-loaded (eg. if the map bounds change)
     *
     * @event load:reset
     */

    var options = _.extend({
      strategy: TileStrategy,
      validate: true
    }, opt_options);


    var attrs = _.extend({
      /**
       * An array of subdomains to use for load balancing tile requests.
       *
       * @attribute subdomains
       * @type {Array.<string>}
       * @abstract
       */
      subdomains: [],


      /**
       * The name of the tile layer.
       *
       * The value of the name can be anything,
       * though some map views will display this name
       * in layer-select controls.
       *
       * @attribute name
       * @type {string}
       * @abstract
       */
      name: undefined,


      /**
       * The server used for requesting tiles. The server will be interpolated by replacing
       * special variables with calculated values. Special variables should be
       * wrapped with '{' and '}'
       *
       * * {d} - a randomly selected subdomain
       *
       * @attribute server
       * @type {string}
       * @abstract
       */
      server: undefined,


      /**
       * The minimum zoom level provided by the tile renderer.
       *
       * @attribute minZoom
       * @type {number}
       * @default 0
       */
      minZoom: 0,


      /**
       * The maximum zoom level provided by the tile renderer.
       *
       * @attribute maxZoom
       * @type {number}
       * @default 22
       */
      maxZoom: 22,


      /**
       * @attribute opacity
       * @type {number} Between 0 and 1.0
       */
      opacity: 1.0,


      /**
       * @attribute zIndex
       * @type {number}
       */
      zIndex: 1
    }, opt_attrs);

    this.listenTo(this, {
      'load': function() {
        this.loaded_ = true;
      },
      'load:reset': function() {
        this.loaded_ = false;
      }
    });

    AnimationLayer.call(this, attrs, options);
  };

  _.inherits(AbstractTile, AnimationLayer);


  /**
   * @method validate
   */
  AbstractTile.prototype.validate = function(attrs) {
    if (!_.isString(attrs.server)) {
      return new ValidationError('server', 'not a valid string');
    }
    if (
      !_.isNumber(attrs.opacity) ||
      attrs.opacity > 1 ||
      attrs.opacity < 0
    ) {
      return new ValidationError('opacity', 'must be a number between 0 and 1');
    }

    return AnimationLayer.prototype.validate.apply(this, arguments);
  };


  /**
   * Returns the url for requesting tiles. The url will be interpolated by replacing
   * special variables with calculated values. Special variables should be
   * wrapped in brackets.
   *
   * * d - a randomly selected subdomain
   * * z - the calculated zoom factor
   * * x - the tile's starting x coordinate
   * * y - the tile's starting y coordinate
   *
   * ex. http://{d}.tileserver.net/{z}/{x}/{y}.png
   *
   * @type {string}
   * @return {string} default url for tile image.
   * @method getUrl
   */
  AbstractTile.prototype.getUrl = _.abstractMethod;


  /**
   * @return {string} A random subdomain for the tile server.
   * @method getRandomSubdomain
   */
  AbstractTile.prototype.getRandomSubdomain = function() {
    var index = Math.floor(Math.random() * this.get('subdomains').length);
    return this.get('subdomains')[index];
  };


  /**
   * Implemented map specific zoom factor calculation.
   *
   * @param {number} zoom the map's current zoom level.
   * @return {number}
   * @method zoomFactor
   */
  AbstractTile.prototype.zoomFactor = function(zoom) {
    return zoom;
  };


  /**
   * Sets the opacity of the tile layer.
   *
   * @param {number} opacity Between 0 and 1.
   * @method setOpacity
   */
  AbstractTile.prototype.setOpacity = function(opacity) {
    this.set('opacity', opacity, { validate: true });
  };

  /**
   * @method getOpacity
   * @return {number}
   */
  AbstractTile.prototype.getOpacity = function() {
    return this.get('opacity');
  };


  /**
   * Sets the zIndex of a tile layer.
   *
   * @type {*}
   * @method setZIndex
   */
  AbstractTile.prototype.setZIndex = function(zIndex) {
    this.set('zIndex', zIndex, { validate: true });
  };


  /**
   * @method getZIndex
   * @return {number}
   */
  AbstractTile.prototype.getZIndex = function() {
    return this.get('zIndex');
  };


  /**
   * @return {Boolean} True, if tile images have finished loading.
   * @method isLoaded
   */
  AbstractTile.prototype.isLoaded = function() {
    return !!this.loaded_;
  };


  /**
   * Preloads the tile layer images.
   *
   * @method preload
   * @param {aeris.maps.Map} map
   *        The layer will be temporarily set to this
   *        map, in order to trigger it's tile images
   *        to start loading.
   */
  AbstractTile.prototype.preload = function(map) {
    if (this.isPreloading_ || this.isLoaded()) {
      return Promise.resolve();
    }
    this.isPreloading_ = true;

    var promiseToLoad = new Promise();
    var attrs_orig = this.pick(['opacity']);
    var attrListener = new Events();

    // We're already loaded
    // -- resolve immediately.
    if (this.isLoaded()) {
      promiseToLoad.resolve();
      return promiseToLoad;
    }
    // We don't have a map to use,
    // so that's all
    if (!map) {
      this.isPreloading_ = false;
      promiseToLoad.reject(new LayerLoadingError('Unable to preload Tile: no map has been specified.'));
      return promiseToLoad;
    }

    this.listenToOnce(this, 'load', function() {
      attrListener.stopListening();
      attrListener.off();

      if (this.hasMap()) {
        this.strategy_.setMap(this.getMap());
      }
      else {
        this.strategy_.remove();
      }

      this.set(attrs_orig);
      this.isPreloading_ = false;
      promiseToLoad.resolve();
    });

    this.set({
      // Temporarily set to 0 opacity, so we don't see
      // the layer being added to the map
      opacity: 0
    });
    // Trigger the layer to load, by setting its
    // view to a map.
    this.strategy_.setMap(map);

    // Listen for any changes made during preloading,
    // so we can make sure to reset our object to the expected state.
    attrListener.listenTo(this, {
      'change:opacity': function(obj, opacity) {
        attrs_orig.opacity = opacity;
      }
    });

    return promiseToLoad;
  };


  return AbstractTile;

});

define('aeris/maps/strategy/layers/osm',[
  'aeris/maps/strategy/layers/tile'
], function(TileStrategy) {
  return TileStrategy;
});

define('aeris/maps/layers/osm',[
  'aeris/util',
  'aeris/maps/layers/abstracttile',
  'aeris/maps/strategy/layers/osm'
], function(_, BaseTile, OSMStrategy) {

  /**
   * Representation of OpenStreetMaps layer.
   *
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.OSM
   * @extends aeris.maps.layers.AbstractTile
   */
  var OSM = function(opt_attrs, opt_options) {
    var options = _.extend({
      strategy: OSMStrategy
    }, opt_options);

    var attrs = _.extend({
      name: 'OpenStreetMap',
      subdomains: ['a', 'b', 'c'],
      server: 'https://{d}.tile.openstreetmap.org',
      maxZoom: 18
    }, opt_attrs);


    BaseTile.call(this, attrs, options);
  };

  _.inherits(OSM, BaseTile);




  /**
   * @method getUrl
   */
  OSM.prototype.getUrl = function() {
    return this.get('server') + '/{z}/{x}/{y}.png';
  };


  return _.expose(OSM, 'aeris.maps.layers.OSM');

});


define('aeris/maps/strategy/util',[
  'aeris/util',
  'leaflet'
], function(_, Leaflet) {
  /**
   * Utility methods for the Aeris Leaflet maps strategy.
   *
   * @class aeris.maps.leaflet.util
   * @static
   */
  var util = {
    /**
     * @method toAerisLatLong
     * @param {L.LatLng} leafletLatLng
     * @return {aeris.maps.LatLon}
     */
    toAerisLatLon: function(leafletLatLng) {
      var wrappedLatLng = leafletLatLng.wrap();
      return [wrappedLatLng.lat, wrappedLatLng.lng];
    },

    /**
     * @method toAerisBounds
     * @param {L.LatLngBounds} leafletBounds
     * @return {aeris.maps.Bounds}
     */
    toAerisBounds: function(leafletBounds) {
      var sw = leafletBounds.getSouthWest();
      var ne = leafletBounds.getNorthEast();
      return [
        util.toAerisLatLon(sw),
        util.toAerisLatLon(ne)
      ];
    },

    /**
     * @param {aeris.maps.LatLon} aerisLatLon
     * @return {L.LatLng}
     */
    toLeafletLatLng: function(aerisLatLon) {
      return new Leaflet.LatLng(aerisLatLon[0], aerisLatLon[1]);
    },

    /**
     * @param {aeris.maps.Bounds} aerisBounds
     * @return {L.LatLngBounds}
     */
    toLeafletBounds: function(aerisBounds) {
      var sw = new Leaflet.LatLng(aerisBounds[0][0], aerisBounds[0][1]);
      var ne = new Leaflet.LatLng(aerisBounds[1][0], aerisBounds[1][1]);

      return new Leaflet.LatLngBounds(sw, ne);
    }
  };

  return util;
});

define('aeris/maps/strategy/map',[
  'aeris/util',
  'aeris/maps/abstractstrategy',
  'aeris/maps/layers/osm',
  'aeris/maps/strategy/util',
  'leaflet'
], function(_, AbstractStrategy, OSMLayer, mapUtil, Leaflet) {
  /**
   * A map rendering strategy using Leaflet.
   *
   * @class aeris.maps.leaflet.Map
   * @extends aeris.maps.strategy.AbstractStrategy
   *
   * @constructor
   */
  var LeafletMapStrategy = function(aerisMap) {
    AbstractStrategy.call(this, aerisMap);

    /**
     * @property object_
     * @private
     * @type {aeris.maps.Map}
     * @override
     */
    /**
     * @property view_
     * @private
     * @type {L.Map}
     * @override
     */

      // We need to allow the map view to finish initializing
      // before asking a layer to be set on it.
    this.ensureBaseLayer_();


    this.updateObjectFromView_();
    this.updateLeafletMapPosition_();

    this.proxyLeafletMapEvents_();
    this.bindToLeafletMapState_();

    this.updateSizeWhenCanasAddedToDOM_();
  };
  _.inherits(LeafletMapStrategy, AbstractStrategy);


  /**
   * @method createView_
   * @private
   * @override
   *
   * @return {L.Map}
   */
  LeafletMapStrategy.prototype.createView_ = function() {
    var map;
    var el = this.object_.getElement();

    // Use predefined map view
    if (el instanceof Leaflet.Map) {
      return el;
    }

    map = new Leaflet.Map(el, {
      center: mapUtil.toLeafletLatLng(this.object_.getCenter()),
      zoom: this.object_.getZoom(),
      scrollWheelZoom: this.object_.get('scrollZoom')
    });

    return map;
  };


  /**
   * Sets a base layer, if none is already set.
   *
   * @method ensureBaseLayer_
   * @private
   */
  LeafletMapStrategy.prototype.ensureBaseLayer_ = function() {
    var baseLayer = this.object_.getBaseLayer();
    if (!baseLayer) {
      baseLayer = new LeafletMapStrategy.DEFAULT_BASE_LAYER_TYPE_();
      this.object_.setBaseLayer(baseLayer);
    }

    if (!this.getLayerCount_()) {
      this.renderBaseLayer_(baseLayer);
    }
  };


  /**
   * @property DEFAULT_BASE_LAYER_TYPE_
   * @static
   * @type {function():aeris.maps.layers.Layer}
   * @private
   */
  LeafletMapStrategy.DEFAULT_BASE_LAYER_TYPE_ = OSMLayer;


  /**
   * @method getLayerCount_
   * @private
   */
  LeafletMapStrategy.prototype.getLayerCount_ = function() {
    var count = 0;

    // We have indirect access to the
    // maps layers via the #eachLayer method.
    this.view_.eachLayer(function() {
      count++;
    });

    return count;
  };


  /**
   * @method updateLeafletMapPosition_
   * @private
   */
  LeafletMapStrategy.prototype.updateLeafletMapPosition_ = function() {
    this.view_.setView(this.object_.getCenter(), this.object_.getZoom());
  };


  /**
   * @method proxyLeafletMapEvents_
   * @private
   */
  LeafletMapStrategy.prototype.proxyLeafletMapEvents_ = function() {
    this.proxyLeafletMouseEvent_('click');
    this.proxyLeafletMouseEvent_('dblclick');

    this.view_.addEventListener({
      load: function() {
        this.object_.trigger('load');
      }.bind(this)
    });
  };

  /**
   * @param {string} leafletTopic
   * @param {string=} opt_aerisTopic
   * @private
   */
  LeafletMapStrategy.prototype.proxyLeafletMouseEvent_ = function(leafletTopic, opt_aerisTopic) {
    var aerisTopic = opt_aerisTopic || leafletTopic;

    this.view_.addEventListener(leafletTopic, function(evt) {
      var latLon = mapUtil.toAerisLatLon(evt.latlng);
      this.object_.trigger(aerisTopic, latLon);
    }.bind(this));
  };


  /**
   * @method bindToLeafletMapState_
   * @private
   */
  LeafletMapStrategy.prototype.bindToLeafletMapState_ = function() {
    this.view_.addEventListener({
      moveend: this.updateObjectFromView_.bind(this)
    });

    this.listenTo(this.object_, {
      'change:center change:zoom': this.updateLeafletMapPosition_,
      'change:baseLayer': this.updateBaseLayer_,
      'updateSize': this.updateSize_
    });
  };


  /**
   * @method updateObjectFromView_
   * @private
   */
  LeafletMapStrategy.prototype.updateObjectFromView_ = function() {
    this.object_.set({
      center: mapUtil.toAerisLatLon(this.view_.getCenter()),
      bounds: mapUtil.toAerisBounds(this.view_.getBounds()),
      zoom: this.view_.getZoom(),
      scrollZoom: this.view_.options.scrollWheelZoom
    }, { validate: true });
  };


  /**
   * @method updateBaseLayer_
   * @private
   */
  LeafletMapStrategy.prototype.updateBaseLayer_ = function() {
    var baseLayer = this.object_.getBaseLayer();
    var previousBaseLayer = this.object_.previousAttributes().baseLayer;
    var isSameBaseLayer = baseLayer === previousBaseLayer;

    if (isSameBaseLayer) {
      return;
    }
    if (previousBaseLayer) {
      this.view_.removeLayer(previousBaseLayer.getView());
      previousBaseLayer.set('map', null, { silent: true });
    }

    this.renderBaseLayer_(baseLayer);
  };


  /**
   * @method renderBaseLayer_
   * @private
   * @param {aeris.maps.layers.Layer} baseLayer
   */
  LeafletMapStrategy.prototype.renderBaseLayer_ = function(baseLayer) {
    this.view_.addLayer(baseLayer.getView(), true);

    // Manually update map attribute, without the base layer
    // trying to update the view itself.
    baseLayer.set('map', this.object_, { silent: true });
  };


  /**
   * @method fitToBounds
   * @param {aeris.maps.Bounds} bounds
   */
  LeafletMapStrategy.prototype.fitToBounds = function(bounds) {
    this.view_.fitBounds(mapUtil.toLeafletBounds(bounds));
  };


  /**
   * @method updateSizeWhenCanasAddedToDOM_
   * @private
   */
  LeafletMapStrategy.prototype.updateSizeWhenCanasAddedToDOM_ = function() {
    var el = this.object_.mapEl_;
    var isElDrawn = function() {
      return el.offsetWidth !== 0 && el.offsetHeight !== 0;
    };

    function pollUntil(predicate, cb, pollInterval) {
      var pollTimer;
      var predicateChecker = function() {
        if (predicate()) {
          cb();
          root.clearInterval(pollTimer);
        }
      };
      pollTimer = root.setInterval(predicateChecker, pollInterval);
      predicateChecker();
    }

    pollUntil(isElDrawn, this.updateSize_.bind(this), 100);
  };

  /**
   * @method updateSize_
   * @private
   */
  LeafletMapStrategy.prototype.updateSize_ = function() {
    this.getView().invalidateSize();
  };


  var root = this;

  return LeafletMapStrategy;
});

define('aeris/maps/map',[
  'aeris/util',
  'aeris/events',
  'aeris/errors/validationerror',
  'aeris/maps/extensions/mapextensionobject',
  'aeris/maps/strategy/map'
], function(_, Events, ValidationError, MapExtensionObject, MapStrategy) {
  /**
   * An Aeris {aeris.maps.Map} is the base object on which all other map objects live. Any {aeris.maps.MapObjectInterface} object can be added to a map using the `setMap` method:
   *
   * <code class="example">
   *   var map = new aeris.maps.Map('map-canvas-id');
   *   mapObject.setMap(map);  // adds the object to the map
   *   mapobject.setMap(null); // removes the object from the map
   * </code>
   *
   * {aeris.maps.markers.Marker} and {aeris.maps.layers.AerisRadar} are examples of {aeris.maps.MapObjectInterface} objects which can be set to a map.
   *
   * @publicApi
   * @class aeris.maps.Map
   * @extends aeris.maps.extensions.MapExtensionObject
   * @override
   *
   * @param {HTMLElement|string} el Map canvas element, by reference or id.
   *
   * @param {Object=} opt_attrs Attributes to set on the map on initialization
   * @param {aeris.maps.LatLon=} opt_attrs.center
   * @param {number=} opt_attrs.zoom
   *
   * @param {Object=} opt_options
   *
   * @constructor
   * @throws {aeris.errors.ValidationError} If no element is supplied.
   */
  var Map = function(el, opt_attrs, opt_options) {
    /**
     * @event click
     * @param {aeris.maps.LatLon} latLon
     */
    /**
     * @event dblclick
     * @param {aeris.maps.LatLon} latLon
     */
    /**
     * When base map tiles are loaded.
     * @event load
     */
    var attrs = _.extend({
      /**
       * @attribute center
       * @type {aeris.maps.LatLon} LatLon coordinate.
       */
      center: [45, -90],


      /**
       * LatLon bounds of the map viewport.
       *
       * @attribute bounds
       * @type {aeris.maps.Bounds} LatLons of SW and NE corners.
       * @default A rough box around the US
       */
      bounds: [
        [22.43, -135.52],
        [52.37, -55.016]
      ],

      /**
       * @attribute zoom
       * @type {number}
       */
      zoom: 4,

      /**
       * Whether to enable zooming using the
       * mouse scrollwheel.
       *
       * Attribute not currently supported by open layers.
       *
       * @attribute scrollZoom
       * @type {Boolean}
       */
      scrollZoom: true,


      /**
       * The base map layer.
       * Note that different mapping libraries have
       * different default base layers.
       *
       * @attribute baseLayer
       * @type {aeris.maps.layers.Layer}
       */
      baseLayer: null
    }, opt_attrs);

    var options = _.extend({
      strategy: MapStrategy,
      validate: true
    }, opt_options);

    /**
     * @property mapEl_
     * @private
     * @type {HTMLElement} Map element be also be a reference to a pre-existing map view
     */
    this.mapEl_ = this.normalizeElement_(el);

    this.validateElementExists_(this.mapEl_);


    Events.call(this);
    MapExtensionObject.call(this, attrs, options);
  };
  _.inherits(Map, MapExtensionObject);
  _.extend(Map.prototype, Events.prototype);


  Map.prototype.validate = function(attrs) {
    if (!_.isArray(attrs.center)) {
      return new ValidationError('center', attrs.center + ' is not a valid coordinate');
    }
    if (!_.isNumber(attrs.zoom)) {
      return new ValidationError('zoom', attrs.zoom + ' is not a valid zoom level');
    }
  };

  /**
   * @method setBounds
   * @param {aeris.maps.Bounds} bounds
   */
  Map.prototype.setBounds = function(bounds) {
    this.set('bounds', bounds, { validate: true });
  };

  /**
   * @method getBounds
   * @return {aeris.maps.Bounds}
   */
  Map.prototype.getBounds = function() {
    return this.get('bounds');
  };


  /**
   * @method getView
   * @return {Object} The view object creating by the mapping library.
   */
  Map.prototype.getView = function() {
    return this.strategy_.getView();
  };

  /**
   * @method setCenter
   * @param {aeris.maps.LatLon} center
   */
  Map.prototype.setCenter = function(center) {
    this.set('center', center, { validate: true });
  };

  /**
   * @method getCenter
   * @return {aeris.maps.LatLon}
   */
  Map.prototype.getCenter = function() {
    return this.get('center');
  };


  /**
   * @method setZoom
   * @param {number} zoom
   */
  Map.prototype.setZoom = function(zoom) {
    this.set('zoom', zoom, { validate: true });
  };

  /**
   * @method getZoom
   * @return {number}
   */
  Map.prototype.getZoom = function() {
    return this.get('zoom');
  };


  /**
   * @method setBaseLayer
   * @param {aeris.maps.layers.Layer} baseLayer
   */
  Map.prototype.setBaseLayer = function(baseLayer) {
    this.set('baseLayer', baseLayer, { validate: true });
  };

  /**
   * @method getBaseLayer
   * @return {aeris.maps.layers.Layer}
   */
  Map.prototype.getBaseLayer = function() {
    return this.get('baseLayer');
  };


  /**
   * Zoom and center the map in order to fit
   * the viewport to the specified bounds.
   *
   * This is currently only supported when using Google Maps.
   *
   * @throws {Error} If fitToBounds is not supported by mapping library.
   * @param {aeris.maps.Bounds} bounds
   */
  Map.prototype.fitToBounds = function(bounds) {
    if (!this.strategy_ || !this.strategy_.fitToBounds) {
      throw Error('Unable to fit to bounds: no fitToBounds strategy has been implemented');
    }

    this.strategy_.fitToBounds(bounds);
  };


  /**
   * @method normalizeElement_
   * @private
   * @param {string|HTMLElement} el Element, or element id.
   * @return {HTMLElement}
   */
  Map.prototype.normalizeElement_ = function(el) {
    return _.isString(el) ? document.getElementById(el) : el;
  };


  /**
   * @throws {aeris.errors.ValidationError}
   * @method validateElementExists_
   * @private
   */
  Map.prototype.validateElementExists_ = function(el) {
    if (!el) {
      throw new ValidationError('el', el + ' is not a valid map canvas element or id');
    }
  };


  /**
   * Note that this may return unexpected results
   * when the map was created with a view instance,
   * instead of an {HTMLElement}.
   *
   * @method getElement
   * @return {HTMLElement} The map canvas element
   */
  Map.prototype.getElement = function() {
    return this.mapEl_;
  };


  /**
   * To be used when the map canvas element
   * has changed dimensions.
   *
   * This will tell the underlying mapping strategy to
   * refresh appropriately (eg. load new tile images to account
   *  for a larger map).
   *
   * @method updateSize
   */
  Map.prototype.updateSize = function() {
    this.trigger('updateSize');
  };


  return _.expose(Map, 'aeris.maps.Map');
});

define('aeris/maps/animations/animationinterface',['aeris/util', 'aeris/events'], function(_, Events) {


  /**
   * Creates a new Animation that will use a Layer Strategy for specific
   * implementation support.
   *
   * @constructor
   * @class aeris.maps.animations.AnimationInterface
   * @interface
   * @uses aeris.Events
   */
  var AnimationInterface = function() {


    Events.call(this);


    /**
     * Fires when all animation tile frames
     * are loaded.
     *
     * Waiting to start an animation until
     * this event is triggered will ensure
     * smooth animations.
     *
     * If an animation is started before all
     * tile frames are loaded, tiles which are
     * not yet loaded will not render until
     * they are loaded.
     *
     * @event load:complete
     */

    /**
     * Fires to indicate progress in
     * preloading tiles.
     *
     * Useful for rendering loading UI
     * to the user, or if you want to buffer
     * an animation to a certain % done.
     *
     * @event load:progress
     * @param {number} Percent complete (where 1.0 is 100%).
     */

    /**
     * @event load:error
     * @param {Error} error
     */

    /**
     * Fires when tile times are loaded
     * for this animation.
     *
     * @event load:times
     * @param {Array.<number>} A list of timestamps.
     *                        for which animation frames
     *                        will be created.
     */

    /**
     * When current time of the animation changes.
     *
     * @event change:time
     * @param {Date} time
     */

    /**
     * @event change:from
     * @param {Date} from
     */
    /**
     * @event change:to
     * @param {Date} to
     */
  };
  _.extend(AnimationInterface.prototype, Events.prototype);


  /**
   * Begin preloading assets required to run the animation.
   *
   * @method preload
   * @return {aeris.Promise} Resolves when preloading is complete.
   */
  AnimationInterface.prototype.preload = _.abstractMethod;


  /**
   * Start the animation.
   *
   * @return {undefined}
   * @method start
   */
  AnimationInterface.prototype.start = _.abstractMethod;


  /**
   * Pause the animation.
   *
   * @return {undefined}
   * @method pause
   */
  AnimationInterface.prototype.pause = _.abstractMethod;


  /**
   * Stop the animation.
   *
   * @return {undefined}
   * @method stop
   */
  AnimationInterface.prototype.stop = _.abstractMethod;


  /**
   * Go to the previous step in the animation.
   *
   * @return {undefined}
   * @method previous
   */
  AnimationInterface.prototype.previous = _.abstractMethod;


  /**
   * Go to the next step in the animation.
   *
   * @return {undefined}
   * @method next
   */
  AnimationInterface.prototype.next = _.abstractMethod;


  /**
   * Go to a specific time in the animation.
   *
   * @param {number} time The time to go to (timestamp)
   * @method goToTime
   */
  AnimationInterface.prototype.goToTime = _.abstractMethod;


  /**
   * @method getCurrentTime
   * @return {?Date} Current time of the animation.
   *         Returns null if the animation has not yet initialized.
   */
  AnimationInterface.prototype.getCurrentTime = _.abstractMethod;


  /**
   * @method setFrom
   * @param {Date|number} from
   */
  AnimationInterface.prototype.setFrom = _.abstractMethod;


  /**
   * @method setTo
   * @param {Date|number} to
   */
  AnimationInterface.prototype.setTo = _.abstractMethod;


  /**
   * @method getFrom
   * @return {Date}
   */
  AnimationInterface.prototype.getFrom = _.abstractMethod;


  /**
   * @method getTo
   * @return {Date}
   */
  AnimationInterface.prototype.getFrom = _.abstractMethod;


  /**
   * @method isAnimating
   * @return {Boolean}
   */
  AnimationInterface.prototype.isAnimating = _.abstractMethod;


  /**
   * Is the animated object set to a map?
   *
   * @method hasMap
   * @return {Boolean}
   */
  AnimationInterface.prototype.hasMap = _.abstractMethod;

  return AnimationInterface;
});

define('aeris/maps/animations/abstractanimation',[
  'aeris/util',
  'aeris/maps/animations/animationinterface',
  'aeris/errors/invalidargumenterror'
], function(_, AnimationInterface, InvalidArgumentError) {
  /**
   * A partially implemented {aeris.maps.animations.AnimationInterface}.
   *
   * @param {Object} opt_options
   * @param {Date} opt_options.from Starting time for the animation.
   * @param {Date} opt_options.to Ending time for the animation.
   * @param {number=} opt_options.limit Maximimum number of time intervals to load.
   * @param {number} opt_options.timestep
   * @param {number} opt_options.speed Number of minutes of weather data
   *        to display within a second.
   * @param {number} opt_options.endDelay Milliseconds to pause between animation loops.
   *
   * @constructor
   * @class aeris.maps.animations.AbstractAnimation
   * @extends aeris.maps.animations.AnimationInterface
   */
  var AbstractAnimation = function(opt_options) {
    var options = _.defaults(opt_options || {}, {
      from: _.now() - (1000 * 60 * 60), // one hour ago
      to: _.now(),
      limit: 20,
      speed: 30,
      timestep: 1000 * 60,
      endDelay: 1000
    });

    /**
     * Number of minutes of weather data
     * to display within a second.
     *
     * @type {number}
     * @private
     * @property speed_
     */
    this.speed_ = options.speed;


    /**
     * Milliseconds between animation frames.
     *
     * @type {number}
     * @private
     * @property timestep_
     */
    this.timestep_ = options.timestep;

    /**
     * Time to wait before repeating animation loop.
     * @type {number} Milliseconds.
     * @private
     * @property endDelay_
     */
    this.endDelay_ = options.endDelay;


    /**
     * Animation start time.
     *
     * @type {Date}
     * @protected
     * @property from_
     */
    this.from_ = options.from;


    /**
     * Animation end time.
     *
     * @default Current time.
     * @type {Date}
     * @protected
     * @property to_
     */
    this.to_ = options.to;


    /**
     * Max number of time "frames"
     * to load and render.
     *
     * @type {number}
     * @protected
     * @property limit_
     */
    this.limit_ = options.limit;


    this.normalizeTimeBounds_();


    /**
     * The time of the current animation frame.
     *
     * @type {number} Timestamp
     * @private
     * @property currentTime_
     */
    this.currentTime_ = Date.now();


    /**
     * A reference to the timer created
     * by window.setInterval
     * @type {number}
     * @private
     * @property animationClock_
     */
    this.animationClock_ = null;


    AnimationInterface.call(this);

    this.keepCurrentTimeInBounds_();
  };

  _.inherits(AbstractAnimation, AnimationInterface);


  /**
   * Start animating the layer.
   *
   * Every second, the layer is animated up
   * by timestep * speed milliseconds.
   * @method start
   */
  AbstractAnimation.prototype.start = function() {
    // Because calling goToTime every second would be
    // clunky, we use a shorter interval time, than
    // adjust our animation increment accordingly.
    var tickInterval = 25;

    if (this.isAnimating()) {
      return;
    }


    // Prevents using endDelay, if we're starting from
    // the end.
    if (this.currentTime_ === this.to_) {
      this.goToTime(this.from_);
    }

    var isEndDelaying = false;
    this.animationClock_ = _.interval(function() {
      var multiplier = tickInterval / 1000;
      var timeIncrement = this.timestep_ * this.speed_ * multiplier;
      var nextTime = this.currentTime_ + timeIncrement;

      // If we're at the end, restart animation
      if (isEndDelaying) {
        return;
      }
      else if (nextTime > this.to_) {
        isEndDelaying = true;
        setTimeout(function() {
          isEndDelaying = false;
          if (this.isAnimating()) {
            this.goToTime(this.from_);
          }
        }.bind(this), this.endDelay_);
      }
      else {
        this.goToTime(nextTime);
      }
    }, tickInterval, this);
  };


  /**
   * @method normalizeTimeBounds_
   * @private
   */
  AbstractAnimation.prototype.normalizeTimeBounds_ = function() {
    if (_.isDate(this.from_)) {
      this.from_ = this.from_.getTime();
    }
    if (_.isDate(this.to_)) {
      this.to_ = this.to_.getTime();
    }
  };


  /**
   * Makes sure that the current time is always within
   * the `from` and `to` bounds of the animation.
   *
   * @method keepCurrentTimeInBounds_
   * @private
   */
  AbstractAnimation.prototype.keepCurrentTimeInBounds_ = function() {
    this.listenTo(this, {
      'change:to': function() {
        if (this.getCurrentTime() > this.getTo()) {
          this.goToTime(this.getTo());
        }
      },
      'change:from': function() {
        if (this.getCurrentTime() < this.getFrom()) {
          this.goToTime(this.getFrom());
        }
      }
    });
  };


  /**
   * Sets the current time to the specified time.
   *
   * Classes extending {aeris.maps.animations.AbstractAnimation}
   * should probably do something more useful here.
   *
   * @param {Date} time
   * @method goToTime
   */
  AbstractAnimation.prototype.goToTime = function(time) {
    this.currentTime_ = _.isDate(time) ? time.getTime() : time;
  };


  /**
   * @method getCurrentTime
   * @return {?Date}
   */
  AbstractAnimation.prototype.getCurrentTime = function() {
    return new Date(this.currentTime_);
  };


  /**
   * Stop animating the layer,
   * and return to the most recent frame
   * @method stop
   */
  AbstractAnimation.prototype.stop = function() {
    this.pause();

    if (!_.isNull(this.to_)) {
      this.goToTime(this.to_);
    }
  };


  /**
   * Stop animation the layer,
   * and stay at the current frame.
   * @method pause
   */
  AbstractAnimation.prototype.pause = function() {
    window.clearInterval(this.animationClock_);
    this.animationClock_ = null;
  };


  /**
   * Set the animation speed.
   *
   * Every second, [timestep] * [speed] milliseconds
   * of tiles are animated.
   *
   * So with a timestep of 360,000 (6 minutes), and a speed of 2:
   *  every second, 12 minutes of tiles will be animated.
   *
   * Setting a negative speed will cause the animation to run in reverse.
   *
   * Also see {aeris.maps.animations.AbstractAnimation}#setTimestamp
   *
   * @param {number} speed
   * @method setSpeed
   */
  AbstractAnimation.prototype.setSpeed = function(speed) {
    if (speed === this.speed_) {
      return;
    }

    if (!_.isNumber(speed)) {
      throw new InvalidArgumentError(speed + ' is not a valid animation speed.');
    }

    this.speed_ = speed;

    if (this.isAnimating()) {
      this.pause();
      this.start();
    }
  };


  /**
   * Sets the animation timestep.
   *
   * See {aeris.maps.animations.AbstractAnimation}#setSpeed
   * for more information on how
   * to use setTimestep and setSpeed to affect your animation speed.
   *
   * @param {number} timestep Timestep, in milliseconds.
   * @method setTimestep
   */
  AbstractAnimation.prototype.setTimestep = function(timestep) {
    if (timestep === this.timestep_) {
      return;
    }

    this.timestep_ = timestep;

    if (this.isAnimating()) {
      this.pause();
      this.start();
    }
  };


  /**
   * @method setFrom
   * @param {Date|number} from
   */
  AbstractAnimation.prototype.setFrom = function(from) {
    var isSame;

    if (from instanceof Date) {
      from = from.getTime();
    }

    isSame = (from === this.from_);

    if (!isSame) {
      this.from_ = from;
      this.trigger('change:from', new Date(this.from_));
    }
  };


  /**
   * @method getFrom
   * @return {Date}
   */
  AbstractAnimation.prototype.getFrom = function() {
    return new Date(this.from_);
  };


  /**
   * @method setTo
   * @param {Date|number} to
   */
  AbstractAnimation.prototype.setTo = function(to) {
    var isSame;

    if (to instanceof Date) {
      to = to.getTime();
    }

    isSame = (to === this.to_);

    if (!isSame) {
      this.to_ = to;
      this.trigger('change:to', new Date(this.to_));
    }
  };


  /**
   * @method getTo
   */
  AbstractAnimation.prototype.getTo = function() {
    return new Date(this.to_);
  };


  /**
   * @return {Boolean} True, if the animation is currently running.
   * @method isAnimating
   */
  AbstractAnimation.prototype.isAnimating = function() {
    return _.isNumber(this.animationClock_);
  };


  return AbstractAnimation;
});

define('aeris/util/findclosest',[
  'aeris/util'
], function(_) {
  /**
   * Find the number closet to a target number.
   *
   * @method findCloset
   * @namespace aeris.util
   *
   * @param {number} target
   * @param {Array.<number>} numbers
   */
  return function findClosest(target, numbers) {
    var numbersInOrderOfDistance = _.clone(numbers).sort(function(a, b) {
      var isAMoreDistantThanB = isMoreDistantThan(a, b, target);

      if (isAMoreDistantThanB) {
        return 1;
      }
      else {
        return -1;
      }
    });

    return numbersInOrderOfDistance[0];
  }

  function getDistance(a, b) {
    return Math.abs(a - b);
  }

  /**
   *
   * @param {Number} a
   * @param {Number} b
   * @param {Number} target
   * @return {Boolean} Returns true if 'a' is more distant than 'b' from target.
   */
  function isMoreDistantThan(a, b, target) {
    var aDistanceFromTarget = getDistance(a, target);
    var bDistanceFromTarget = getDistance(b, target);

    return aDistanceFromTarget > bDistanceFromTarget;
  }
});

define('aeris/maps/animations/tileanimation',[
  'aeris/util',
  'aeris/maps/animations/abstractanimation',
  'aeris/promise',
  'aeris/errors/invalidargumenterror',
  'aeris/util/findclosest'
], function(_, AbstractAnimation, Promise, InvalidArgumentError, findClosest) {
  /**
   * Animates a single {aeris.maps.layers.AerisTile} layer.
   *
   * @publicApi
   * @class aeris.maps.animations.TileAnimation
   * @constructor
   * @extends aeris.maps.animations.AbstractAnimation
   *
   * @param {aeris.maps.layers.AerisTile} layer The layer to animate.
   * @param {aeris.maps.animations.options.AnimationOptions} opt_options
   * @param {number} opt_options.timestep Time between animation frames, in milliseconds.
   * @param {aeris.maps.animations.helpers.AnimationLayerLoader=} opt_options.animationLayerLoader
   */
  var TileAnimation = function(layer, opt_options) {
    var options = opt_options || {};

    AbstractAnimation.call(this, options);


    /**
     * The original layer object, which will serve as
     * the 'master' for all animation layer "frames."
     *
     * @type {aeris.maps.layers.AerisTile}
     * @private
     * @property masterLayer_
     */
    this.masterLayer_ = layer;


    /**
     * @property currentLayer_
     * @private
     * @type {?aeris.maps.layers.AerisTile}
     */
    this.currentLayer_ = null;


    /**
     * A hash of {aeris.maps.layers.AerisTile},
     * listed by timestamp.
     *
     * @type {Object.<number,aeris.maps.layers.AerisTile>}
     * @private
     * @property layersByTime_
     */
    this.layersByTime_ = {};


    /**
     * An array of available timestamps.
     *
     * @type {Array.number}
     * @private
     * @property times_
     */
    this.times_ = [];


    // Convert the master layer into a "dummy" view model,
    // with no bound rendering behavior.
    //
    // This will allow the client to manipulate the master layer
    // as a proxy for all other animation frames, without actually
    // showing the layer on the map.
    //
    this.masterLayer_.removeStrategy();

    // Load all the tile layers for the animation
    this.loadAnimationLayers();

    // Reload layers, when our bounds change
    this.listenTo(this, 'change:to change:from', function() {
      this.loadAnimationLayers();
    });

    // Make sure the current layer is loaded, when the masterLayer get a map
    this.listenTo(this.masterLayer_, 'map:set', function() {
      this.preloadLayer_(this.getCurrentLayer());
    });
  };
  _.inherits(TileAnimation, AbstractAnimation);

  /**
   * @property DEFAULT_TIME_TOLERANCE_
   * @static
   * @type {number}
   * @private
   */
  TileAnimation.DEFAULT_TIME_TOLERANCE_ = 1000 * 60 * 60 * 2; // 2 hours

  /**
   * Load the tile layers for the animation.
   *
   * @return {aeris.Promise} Promise to load all layers.
   * @method loadAnimationLayers
   */
  TileAnimation.prototype.loadAnimationLayers = function() {
    var prevCurrentTime = this.getCurrentTime();
    var prevLayersByTime = this.layersByTime_;

    // Create new layers
    var nextTimes = getTimeRange(this.from_, this.to_, this.limit_);
    var nextLayers = nextTimes.reduce(function(lyrs, time) {
      lyrs[time] = this.masterLayer_.clone({
        time: new Date(time),
        map: null,
        autoUpdate: false
      });
      return lyrs;
    }.bind(this), {});

    // Wait for new current layer to load
    var nextCurrentTime = this.getClosestTime_(prevCurrentTime, nextTimes);
    var nextCurrentLayer = nextLayers[nextCurrentTime];

    this.times_ = nextTimes;
    this.layersByTime_ = nextLayers;
    this.bindLayerLoadEvents_();

    this.trigger('load:times', this.times_.slice(0));

    // If there's already layers loaded,
    // preload the new layers, before transitioning.
    // This prevents a "flash" when change bounds
    // (eg. when AIM autoUpdate triggers)
    var transition = (function() {
      this.goToTime(this.getCurrentTime());
      // Remove old layers
      _.each(prevLayersByTime, function(lyr) {
        lyr.destroy();
      });
    }.bind(this));
    if (this.getCurrentLayer()) {
      this.preloadLayer_(nextCurrentLayer)
        .always(function() {
          setTimeout(transition, 1000);
        }, this);
    }
    else {
      transition();
    }
  };

  TileAnimation.prototype.bindLayerLoadEvents_ = function() {
    var triggerLoadReset = _.debounce(function() {
      this.trigger('load:reset', this.getLoadProgress());
    }.bind(this), 15);

    var triggerLoadProgress = (function() {
      var progress = this.getLoadProgress();
      if (progress === 1) {
        this.trigger('load:complete', progress);
      }

      this.trigger('load:progress', progress);
    }.bind(this));

    _.each(this.layersByTime_, function(lyr) {
      lyr.on({
        'load': triggerLoadProgress,
        'load:reset': triggerLoadReset
      });
    }.bind(this));
  };

  /**
   * @method preload
   */
  TileAnimation.prototype.preload = function() {
    var promiseToPreload = new Promise();

    var layers = _.values(this.layersByTime_);

    // Then preload the rest
    var layersToPreload = [this.getCurrentLayer()].concat(_.shuffle(layers));
    Promise.map(layersToPreload, this.preloadLayer_, this)
      .done(promiseToPreload.resolve)
      .fail(promiseToPreload.reject);

    return promiseToPreload;
  };


  /**
   * Preloads a single tile layer.
   *
   * @method preloadLayer_
   * @private
   * @param {aeris.maps.layers.AerisTile} layer
   * @return {aeris.Promise} Promise to load the layer
   */
  TileAnimation.prototype.preloadLayer_ = function(layer) {
    var promiseToPreload = new Promise();

    var doPreload = (function() {
      // Add the layer to the map
      // with opacity 0 (to trigger loading)
      layer.set({
        map: this.masterLayer_.getMap(),
        opacity: layer === this.getCurrentLayer() ? this.masterLayer_.getOpacity() : 0
      });

      // Resolve once we're done loading
      layer.once('load', promiseToPreload.resolve);

      // Give up after a while, so we're not blocking the animation
      setTimeout(function() {
        if (promiseToPreload.getState() === 'pending') {
          // Just call the layer loaded (show whatever we've got)
          layer.trigger('load');
        }
      }.bind(this), 2000);
    }.bind(this));


    // Wait for the last layer to preload,
    // before preloading the next one
    this.lastPromiseToPreload_ || (this.lastPromiseToPreload_ = Promise.resolve());
    this.lastPromiseToPreload_
      .always(function() {
        // If the layer is already loaded, no more work to do.
        if (layer.isLoaded()) {
          promiseToPreload.resolve();
        }
        // Wait for a map to be set, before preloading
        else if (!this.masterLayer_.getMap()) {
          this.masterLayer_.once('map:set', doPreload);
        }
        else {
          doPreload();
        }
      }, this);

    this.lastPromiseToPreload_ = promiseToPreload;
    return promiseToPreload;
  };



  /**
   * @method refreshCurrentLayer_
   * @private
   */
  TileAnimation.prototype.refreshCurrentLayer_ = function() {
    this.goToTime(this.getCurrentTime());
  };

  TileAnimation.prototype.start = function() {
    AbstractAnimation.prototype.start.call(this);
    // preload on start
    // (this gives a little better ux than allowing layers to preload
    //  as we hit them, because `preload()` shuffles the layer order)
    this.preload();
  };


  /**
   * Animates to the layer at the next available time,
   * or loops back to the start.
   * @method next
   */
  TileAnimation.prototype.next = function() {
    var nextTime = this.getNextTime_();

    if (!nextTime) {
      return;
    }

    this.goToTime(nextTime);
  };


  /**
   * Animates to the previous layer,
   * or loops back to the last layer.
   * @method previous
   */
  TileAnimation.prototype.previous = function() {
    var prevTime = this.getPreviousTime_();

    if (!prevTime) {
      return;
    }

    this.goToTime(prevTime);
  };


  /**
   * Animates to the layer at the specified time.
   *
   * If no layer exists at the exact time specified,
   * will use the closest available time.
   *
   * @param {number|Date} time UNIX timestamp or date object.
   * @method goToTime
   */
  TileAnimation.prototype.goToTime = function(time) {
    var nextLayer;
    var currentLayer;
    var haveTimesBeenLoaded = !!this.getTimes().length;

    time = _.isDate(time) ? time.getTime() : time;

    if (!_.isNumeric(time)) {
      throw new InvalidArgumentError('Invalid animation time: time must be a Date or a timestamp (number).');
    }

    currentLayer = this.getCurrentLayer();
    // Note that we may not be able to find a layer in the same tense,
    // in which case this value is null.
    nextLayer = this.getLayerForTimeInSameTense_(time) || null;

    // Set the new layer
    this.currentTime_ = time;

    if (nextLayer === currentLayer) {
      return;
    }

    // If no time layers have been created
    // wait for time layers to be created,
    // then try again. Otherwise, our first
    // frame will  never show.
    if (!haveTimesBeenLoaded) {
      this.listenToOnce(this, 'load:times', function() {
        this.goToTime(this.getCurrentTime());
      });
    }

    if (nextLayer) {
      this.transition_(currentLayer, nextLayer);
    }
    else if (currentLayer) {
      this.transitionOut_(currentLayer);
    }

    this.currentLayer_ = nextLayer;
    this.trigger('change:time', new Date(this.currentTime_));
  };


  /**
   * @return {number} Percentage complete loading tile (1.0 is 100% complete).
   * @method getLoadProgress
   */
  TileAnimation.prototype.getLoadProgress = function() {
    var totalCount = _.keys(this.layersByTime_).length;
    var loadedCount = 0;

    if (!totalCount) {
      return 0;
    }

    _.each(this.layersByTime_, function(layer) {
      if (layer.isLoaded()) {
        loadedCount++;
      }
    }, 0);


    return Math.min(loadedCount / totalCount, 1);
  };


  /**
   * @method hasMap
   */
  TileAnimation.prototype.hasMap = function() {
    return this.masterLayer_.hasMap();
  };


  /**
   * Destroys the tile animation object,
   * clears animation frames from memory.
   *
   *
   * @method destroy
   */
  TileAnimation.prototype.destroy = function() {
    _.invoke(this.layersByTime_, 'destroy');
    this.layersByTime_ = {};
    this.times_.length = 0;

    this.stopListening();

    this.masterLayer_.resetStrategy();
  };


  /**
   * Returns available times.
   * Note that times are loaded asynchronously from the
   * Aeris Interactive Tiles API, so they will not be immediately
   * available.
   *
   * Wait for the 'load:times' event to fire before attempting
   * to grab times.
   *
   * @return {Array.<number>} An array timestamps for which
   *                          they are available tile frames.
   * @method getTimes
   */
  TileAnimation.prototype.getTimes = function() {
    return _.clone(this.times_);
  };


  /**
   * @method getLayerForTimeInSameTense_
   * @private
   * @param {Number} time
   * @return {aeris.maps.layers.AerisTile}
   */
  TileAnimation.prototype.getLayerForTimeInSameTense_ = function(time) {
    return this.layersByTime_[this.getClosestTimeInSameTense_(time)];
  };


  /**
   * Returns the closes available time.
   *
   * @param {number} targetTime UNIX timestamp.
   * @param {Array.<Number>} opt_times Defaults to loaded animation times.
   * @return {number}
   * @private
   * @method getClosestTime_
   */
  TileAnimation.prototype.getClosestTime_ = function(targetTime, opt_times) {
    var times = opt_times || this.times_;
    return findClosest(targetTime, times);
  };


  /**
   * Returns the closes available time.
   *
   * If provided time is in the past, will return
   * the closest past time (and vice versa);
   *
   * @method getClosestTimeInSameTense_
   * @private
   *
   * @param {number} targetTime UNIX timestamp.
   * @param {Array.<Number>} opt_times Defaults to loaded animation times.
   * @return {number}
   */
  TileAnimation.prototype.getClosestTimeInSameTense_ = function(targetTime, opt_times) {
    var isTargetInFuture = targetTime > Date.now();
    var times = opt_times || this.times_;

    // Only look at times that are in the past, if
    // the target is in the past, and vice versa.
    var timesInSameTense = times.filter(function(time) {
      var isTimeInFuture = time > Date.now();
      var isTimeInSameTenseAsTarget = isTimeInFuture && isTargetInFuture || !isTimeInFuture && !isTargetInFuture;

      return isTimeInSameTenseAsTarget;
    });

    return findClosest(targetTime, timesInSameTense);
  };


  /**
   * Transition from one layer to another.
   *
   * @param {?aeris.maps.layers.AerisTile} opt_oldLayer
   * @param {aeris.maps.layers.AerisTile} newLayer
   * @private
   * @method transition_
   */
  TileAnimation.prototype.transition_ = function(opt_oldLayer, newLayer) {


    // If the new layer is not yet loaded,
    // wait to transition until it is.
    // This prevents displaying an "empty" tile layer,
    // and makes it easier to start animations before all
    // layers are loaded.
    if (!newLayer.isLoaded()) {
      this.preloadLayer_(newLayer);
      this.transitionWhenLoaded_(opt_oldLayer, newLayer);
    }


    // Hide all the layers
    // Sometime we have trouble with old layers sticking around.
    // especially when we need to reload layers for new bounds.
    // This a fail-proof way to handle that issue.
    _.without(this.layersByTime_, newLayer).
      forEach(this.transitionOut_, this);

    this.transitionInClosestLoadedLayer_(newLayer);
  };


  /**
   * @param {aeris.maps.layers.AerisTile} layer
   * @method transitionIn_
   * @private
   */
  TileAnimation.prototype.transitionIn_ = function(layer) {
    this.syncLayerToMaster_(layer);
  };


  /**
   * @param {aeris.maps.layers.AerisTile} layer
   * @method transitionOut_
   * @private
   */
  TileAnimation.prototype.transitionOut_ = function(layer) {
    layer.stopListening(this.masterLayer_);
    layer.setOpacity(0);
  };


  /**
   * Handle transition for a layer which has not yet
   * been loaded
   *
   * @param {?aeris.maps.layers.AerisTile} opt_oldLayer
   * @param {aeris.maps.layers.AerisTile} newLayer
   * @method transitionWhenLoaded_
   * @private
   */
  TileAnimation.prototype.transitionWhenLoaded_ = function(opt_oldLayer, newLayer) {
    // Clear any old listeners from this transition
    // (eg. if transition is called twice for the same layer)
    this.stopListening(newLayer, 'load');
    this.listenToOnce(newLayer, 'load', function() {
      if (this.getCurrentLayer() === newLayer) {
        this.transition_(opt_oldLayer, newLayer);
      }
    });
  };


  /**
   * @method transitionInClosestLoadedLayer_
   * @private
   */
  TileAnimation.prototype.transitionInClosestLoadedLayer_ = function(layer) {
    var loadedTimes = _.keys(this.layersByTime_).filter(function(time) {
      return this.layersByTime_[time].isLoaded();
    }, this);
    var closestLoadedTime = this.getClosestTimeInSameTense_(layer.get('time').getTime(), loadedTimes);

    if (!closestLoadedTime) {
      return;
    }


    this.transitionIn_(this.layersByTime_[closestLoadedTime]);
  };


  /**
   * Update the attributes of the provided layer
   * to match those of the master layer.
   *
   * @method syncLayerToMaster_
   * @private
   * @param  {aeris.maps.layers.AerisTile} layer
   */
  TileAnimation.prototype.syncLayerToMaster_ = function(layer) {
    var boundAttrs = [
      'map',
      'opacity',
      'zIndex'
    ];

    // clear any old bindings
    layer.stopListening(this.masterLayer_);

    layer.bindAttributesTo(this.masterLayer_, boundAttrs);
  };


  TileAnimation.prototype.getCurrentLayer = function() {
    return this.currentLayer_;
  };


  TileAnimation.prototype.getNextTime_ = function() {
    return this.isCurrentLayerLast_() ?
      this.times_[0] : this.times_[this.getLayerIndex_() + 1];
  };


  TileAnimation.prototype.getPreviousTime_ = function() {
    var lastTime = _.last(this.times_);
    return this.isCurrentLayerFirst_() ?
      lastTime : this.times_[this.getLayerIndex_() - 1];
  };

  /**
   * @method isCurrentLayer_
   * @private
   * @param {aeris.maps.layers.AerisTile} layer
   * @return {Boolean}
   */
  TileAnimation.prototype.isCurrentLayer_ = function(layer) {
    return layer === this.getCurrentLayer();
  };


  /**
   * @return {boolean} True, if the current layer is the first frame.
   * @private
   * @method isCurrentLayerFirst_
   */
  TileAnimation.prototype.isCurrentLayerFirst_ = function() {
    return this.getLayerIndex_() === 0;
  };


  /**
   * @return {boolean} True, if the current layer is the last frame.
   * @private
   * @method isCurrentLayerLast_
   */
  TileAnimation.prototype.isCurrentLayerLast_ = function() {
    return this.getLayerIndex_() === this.times_.length - 1;
  };


  /**
   * Returns the index of the current layer
   * within this.times_.
   *
   * @return {number}
   * @private
   * @method getLayerIndex_
   */
  TileAnimation.prototype.getLayerIndex_ = function(layer) {
    layer || (layer = this.getCurrentLayer());
    return this.times_.indexOf(layer.get('time').getTime());
  };

  function getTimeRange(from, to, limit) {
    var animationDuration = to - from;
    var MIN_INTERVAL = 1000 * 60;
    var animationInterval = Math.max(Math.floor(animationDuration / limit), MIN_INTERVAL);

    return _.range(from, to, animationInterval);
  }


  return _.expose(TileAnimation, 'aeris.maps.animations.TileAnimation');
});

define('aeris/maps/animations/autoupdateanimation',[
  'aeris/util',
  'aeris/maps/animations/tileanimation'
], function(_, TileAnimation) {
  /**
   * An AutoUpdateAnimation is automatically updated
   * to to display the most current tiles available
   * from the Aeris API.
   *
   * The timespan (to - from) of an AutoUpdateAnimation
   * object will always remain constant.
   *
   * For example:
   *
   *    var animation = new AutoUpdateAnimation({
   *      from: 1PM_TODAY
   *      to: 3PM_TODAY
   *    });
   *
   *    // Some time passes...
   *    // Tiles become available for 4PM_TODAY
   *    animation.getTo();      // 4PM_TODAY
   *    animation.getFrom();    // 2PM_TODAY
   *
   * Note that as the animation range is updated, it will trigger
   * 'change:from' and 'change:to' events. This is useful if you need
   * UI components to reflect the range of the animation.
   *
   *    animation.on('change:from change:to', function() {
   *      $('#rangeInput').attr('min', animation.getFrom().getTime());
   *      $('#rangeInput').attr('max', animation.getFrom().getTime());
   *    });
   *
   * @class aeris.maps.animations.AutoUpdateAnimation
   * @extends aeris.maps.animations.TileAnimation
   *
   * @constructor
   */
  var AutoUpdateAnimation = function(masterLayer, opt_options) {
    TileAnimation.call(this, masterLayer, opt_options);


    this.bindToLayerAutoUpdate_();

    /**
     * @event autoUpdate
     */
  };
  _.inherits(AutoUpdateAnimation, TileAnimation);


  /**
   * @method bindToLayerAutoUpdate_
   * @private
   */
  AutoUpdateAnimation.prototype.bindToLayerAutoUpdate_ = function() {
    var updateInterval = this.masterLayer_.get('autoUpdateInterval');

    this.listenTo(this.masterLayer_, 'autoUpdate', function() {
      // Bump forward the animation by the autoUpdateInterval
      this.setTo(this.to_ + updateInterval);
      this.setFrom(this.from_ + updateInterval);

      this.listenToOnce(this, 'load:times', function() {
        this.trigger('autoUpdate', this);
      });

      // Reload layers with new interval
      this.loadAnimationLayers();
    });
  };


  return AutoUpdateAnimation;
});

define('aeris/maps/animations/animationsync',[
  'aeris/util',
  'aeris/maps/animations/abstractanimation',
  'aeris/maps/layers/animationlayer',
  'aeris/maps/animations/autoupdateanimation',
  'aeris/promise'
], function(_, AbstractAnimation, AnimationLayer, AutoUpdateAnimation, Promise) {
  /**
   * Animates multiple layers along a single timeline.
   * Works by running a single 'master' animation, and having
   * all other animations go to the same time as the master.
   *
   * The master animation is dynamically set as the animation
   * with the shortest average interval between time frames. You can
   * manually set the master animation using the setMaster method, as well.
   *
   * @param {Array<aeris.maps.layers.AnimationLayer|aeris.maps.animations.AnimationInterface>=} opt_animations Layers/Animations to sync.
   *        Animations can also be added using the `add` method.
   *
   * @param {function():aeris.maps.animations.AnimationInterface} opt_options.AnimationType_ The
   *        type (constructor) of animation object to create when adding a layer to the AnimationSync.
   *
   *
   * @constructor
   * @publicApi
   * @class aeris.maps.animations.AnimationSync
   * @extends aeris.maps.animations.AbstractAnimation
   * @implements aeris.maps.animations.AnimationInterface
   */
  var AnimationSync = function(opt_animations, opt_options) {
    var options = _.defaults(opt_options || {}, {
      AnimationType: AutoUpdateAnimation
    });

    AbstractAnimation.call(this, opt_options);


    /**
     * Reference to the original options
     * passed to the AnimationSync constructor.
     *
     * @type {Object}
     * @private
     * @property options_
     */
    this.options_ = {
      to: this.to_,
      from: this.from_,
      limit: this.limit_,
      timeTolerance: options.timeTolerance
    };


    /**
     * LayerAnimation instance.
     *
     * @type {Array.<Object.<string,aeris.maps.animations.AnimationInterface>>} { 'layerCid': animation }.
     * @private
     * @property animations_
     */
    this.animations_ = [];


    /**
     * Type of animation object to use when adding
     * a layer.
     *
     * @property AnimationType_
     * @private
     * @type {function():aeris.maps.animations.AnimationInterface}
     */
    this.AnimationType_ = options.AnimationType;


    /**
     * Memory of which animations have triggered a load:times event.
     *
     * @property animationsWhichHaveLoadedTimes_
     * @private
     * @type {Array.<aeris.maps.animations.AnimationInterface}
     */
    this.animationsWhichHaveLoadedTimes_ = [];


    // Add animations passed in constructor
    this.add(opt_animations || []);


    this.listenTo(this, {
      'change:to change:from': function() {
        this.animations_.forEach(function(anim) {
          anim.setTo(this.getTo());
          anim.setFrom(this.getFrom());
        }, this);
      }
    });

    /**
     * @event autoUpdate
     */
  };

  _.inherits(AnimationSync, AbstractAnimation);


  /**
   * @method preload
   */
  AnimationSync.prototype.preload = function() {
    var activeAnimations = this.animations_.filter(function(anim) {
      return anim.hasMap();
    });

    // Preload each animation, in sequence.
    return Promise.sequence(activeAnimations, function(animation) {
      return animation.preload();
    });
  };


  /**
   * Add one or more animations to the sync.
   *
   * @param {Array.<aeris.maps.animations.AnimationInterface|aeris.maps.layers.AnimationLayer>} animations_or_layers Animation
   *        object or layer (or an array of objects).
   * @method add
   */
  AnimationSync.prototype.add = function(animations_or_layers) {
    var animations;

    // Normalize as array
    animations_or_layers = _.isArray(animations_or_layers) ?
      animations_or_layers : [animations_or_layers];

    // Normalize as animation objects
    animations = animations_or_layers.map(function(obj) {
      var isLayer = obj instanceof AnimationLayer;

      var options = _.extend({}, this.options_, {
        to: this.getTo(),
        from: this.getFrom(),
        limit: this.limit_
      });
      return isLayer ? new this.AnimationType_(obj, options) : obj;
    }, this);

    _.each(animations, this.addOne_, this);
  };


  /**
   * Add a single animation to sync.
   *
   * @param {aeris.maps.animations.AnimationInterface} animation
   * @private
   * @method addOne_
   */
  AnimationSync.prototype.addOne_ = function(animation) {
    animation.stop();
    this.animations_.push(animation);

    this.listenTo(animation, {
      'load:times': function() {
        if (!_.contains(this.animationsWhichHaveLoadedTimes_, animation)) {
          this.animationsWhichHaveLoadedTimes_.push(animation);
        }

        if (this.animationsWhichHaveLoadedTimes_.length === this.animations_.length) {
          this.trigger('load:times', this.getTimes());
        }
      },
      'load:progress load:complete': this.triggerLoadProgress_,
      'load:error': function(err) {
        this.trigger('load:error', err);
      },
      'load:reset': function(progress) {
        this.trigger('load:reset', progress);
      },
      'autoUpdate': function(anim) {
        this.setTo(anim.getTo());
        this.setFrom(anim.getFrom());
        this.trigger('autoUpdate');
      }
    });

    animation.setTo(this.getTo());
    animation.setFrom(this.getFrom());

    this.triggerLoadProgress_();
  };


  /**
   * Stop syncing one or more animations
   *
   * @param {aeris.maps.animations.AnimationInterface|Array.<aeris.maps.animations.AnimationInterface>} animations
   * @method remove
   */
  AnimationSync.prototype.remove = function(animations) {
    animations = _.isArray(animations) ? animations : [animations];

    _.each(animations, this.removeOne_, this);
  };


  /**
   * Stop syncing a single animation.
   *
   * @param {aeris.maps.animations.AnimationInterface} animation
   * @private
   * @method removeOne_
   */
  AnimationSync.prototype.removeOne_ = function(animation) {
    this.stopListening(animation);

    this.animations_ = _.without(this.animations_, animation);
    animation.stop();
  };


  /**
   * Recalculates the total load progress
   * of all animations.
   *
   * Fires 'load:complete' and 'load:progress' events
   * @method triggerLoadProgress_
   * @private
   */
  AnimationSync.prototype.triggerLoadProgress_ = function() {
    var progress = this.getLoadProgress();

    if (progress >= 1) {
      this.trigger('load:complete');
    }
    this.trigger('load:progress', progress);

    return progress;
  };


  /**
   * Get the total loading progress of animations within the animation
   * sync. Only considers animations which are set to the map.
   *
   * @method getLoadProgress
   * @return {number}
   */
  AnimationSync.prototype.getLoadProgress = function() {
    var activeAnimations = this.animations_.filter(function(anim) {
      return anim.hasMap();
    });
    var progressCounts = activeAnimations.map(function(anim) {
      return anim.getLoadProgress();
    });

    return _.average(progressCounts);
  };


  /**
   * @method next
   * @param {number=} opt_timestep Milliseconds to advance.
   */
  AnimationSync.prototype.next = function(opt_timestep) {
    var timestep = opt_timestep || this.timestep_;
    var nextTime = this.getNextTime_(this.currentTime_, timestep);

    this.goToTime(nextTime);
  };


  /**
   * @method getNextTime_
   * @private
   * @param {number} baseTime
   * @param {number} timestep Milliseconds to advance past base time.
   * @return {number} Next available time. If time is greater than 'to' bound, starts over at 'from'.
   */
  AnimationSync.prototype.getNextTime_ = function(baseTime, timestep) {
    var nextTime = baseTime + timestep;

    // We're already at end
    // --> restart
    if (baseTime >= this.to_) {
      // Reset back to start.
      nextTime = this.from_;
    }

    // Our next time is outside our 'to' bound
    // --> go to end
    else if (nextTime >= this.to_) {
      nextTime = this.to_;
    }

    return nextTime;
  };


  /**
   * @method previous
   * @param {number=} opt_timestep Milleseconds to rewind.
   */
  AnimationSync.prototype.previous = function(opt_timestep) {
    var timestep = opt_timestep || this.timestep_;
    var prevTime = this.getPrevTime_(this.currentTime_, timestep);

    this.goToTime(prevTime);
  };


  /**
   * @method getPrevTime_
   * @private
   * @param {number} baseTime
   * @param {number} timestep Milliseconds to reverse before base time.
   * @return {number} Next available time. If time is less than 'from' bound, starts over at 'to'.
   */
  AnimationSync.prototype.getPrevTime_ = function(baseTime, timestep) {
    var prevTime = baseTime - timestep;

    // We're already at beginning
    // --> go to end
    if (baseTime <= this.from_) {
      prevTime = this.to_;
    }

    // Next time is before beginning
    // --> go to beginning
    else if (prevTime <= this.from_) {
      prevTime = this.from_;
    }

    return prevTime;
  };


  /**
   * @method goToTime
   */
  AnimationSync.prototype.goToTime = function(time) {
    this.currentTime_ = _.isDate(time) ? time.getTime() : time;

    // Move all animations to the current time
    _.each(this.animations_, function(anim) {
      anim.goToTime(this.currentTime_);
    }, this);

    this.trigger('change:time', new Date(this.currentTime_));
  };


  /**
   * @return {Array.<number>} UNIX timestamps. Sorted list of availble animation times.
   * @method getTimes
   */
  AnimationSync.prototype.getTimes = function() {
    var times = [];

    _.each(this.animations_, function(anim) {
      times = times.concat(anim.getTimes());
    });

    return _.sortBy(times, function(n) {
      return n;
    });
  };


  return _.expose(AnimationSync, 'aeris.maps.animations.AnimationSync');
});

define('aeris/packages/animations',[
  'aeris/maps/animations/tileanimation',
  'aeris/maps/animations/animationsync'
], function() {});

define('aeris/togglebehavior',[
  'aeris/util'
], function(_) {
  /**
   * A toggle-able model.
   *
   * Should only be mixed into {aeris.Model} object.
   *
   * @class aeris.ToggleBehavior
   * @extensionfor {aeris.Model}
   * @constructor
   */
  var ToggleBehavior = function() {
    /**
     * @attribute selected
     * @type {Boolean}
     * @default false
     */


    /**
     * @event select
     * @param {aeris.Model} model The selected model.
     */
    /**
     * @event deselect
     * @param {aeris.Model} model The deselected model.
     */
    /**
     * @event change:selected
     * @param {aeris.Model} model The selected or deselected model.
     * @param {Boolean} isSelected
     */

  };

  /**
   * @method initialize
   */
  ToggleBehavior.prototype.initialize = function() {
    if (!this.has('selected')) {
      this.set('selected', false);
    }

    this.bindToggleEvents_();
  };


  /**
   * @method bindToggleEvents_
   * @private
  */
  ToggleBehavior.prototype.bindToggleEvents_ = function() {
    this.listenTo(this, {
      'change:selected': function(model, value) {
        var topic = value ? 'select' : 'deselect';
        this.trigger(topic, model);
      }
    });
  };


  /**
   * Mark as selected.
   * @method select
   */
  ToggleBehavior.prototype.select = function() {
    this.set('selected', true);
  };


  /**
   * Mark as not selected.
   * @method deselect
   */
  ToggleBehavior.prototype.deselect = function() {
    this.set('selected', false);
  };


  /**
   * Toggle the selected attribute
   * @method toggle
   */
  ToggleBehavior.prototype.toggle = function() {
    this.set('selected', !this.get('selected'));
  };


  /**
   * @return {Boolean}
   * @method isSelected
   */
  ToggleBehavior.prototype.isSelected = function() {
    return this.get('selected');
  };


  return ToggleBehavior;
});

define('aeris/maps/strategy/markers/marker',[
  'aeris/util',
  'aeris/maps/abstractstrategy',
  'aeris/maps/strategy/util',
  'leaflet'
], function(_, AbstractStrategy, mapUtil, Leaflet) {
  /**
   * A strategy for rendering a marker using Leaflet.
   *
   * @class aeris.maps.leaflet.markers.Marker
   * @extends aeris.maps.AbstractStrategy
   *
   * @constructor
   */
  var MarkerStrategy = function(mapObject) {
    AbstractStrategy.call(this, mapObject);

    this.bindMarkerAttributes_();
    this.proxyMarkerEvents_();
  };
  _.inherits(MarkerStrategy, AbstractStrategy);


  /**
   * @method createView_
   * @private
   */
  MarkerStrategy.prototype.createView_ = function() {
    var latLng = mapUtil.toLeafletLatLng(this.object_.getPosition());
    return new Leaflet.Marker(latLng, {
      icon: this.createIcon_(),
      clickable: this.object_.get('clickable'),
      draggable: this.object_.get('draggable'),
      title: this.object_.get('title'),
      alt: this.object_.get('title')
    });
  };


  /**
   * @method setMap
   */
  MarkerStrategy.prototype.setMap = function(map) {
    AbstractStrategy.prototype.setMap.call(this, map);

    this.view_.addTo(this.mapView_);
  };


  /**
   * @method beforeRemove_
   * @private
   */
  MarkerStrategy.prototype.beforeRemove_ = function() {
    this.mapView_.removeLayer(this.view_);
  };


  /**
   * @method createIcon_
   * @private
   * @return {L.Icon}
   */
  MarkerStrategy.prototype.createIcon_ = function() {
    return new Leaflet.Icon({
      iconUrl: this.getMarkerUrl_(),
      iconAnchor: this.createOffsetPoint_()
    });
  };


  /**
   * Create a Leafet Point object corresponding
   * to the marker's current offset attributes.
   *
   * @method createOffsetPoint_
   * @private
   * @return {L.Point}
   */
  MarkerStrategy.prototype.createOffsetPoint_ = function() {
    var offset = this.object_.isSelected() ?
      [this.object_.get('selectedOffsetX'), this.object_.get('selectedOffsetY')] :
      [this.object_.get('offsetX'), this.object_.get('offsetY')];

    return Leaflet.point.apply(Leaflet.point, offset);
  };


  /**
   * @method getMarkerUrl_
   * @private
   */
  MarkerStrategy.prototype.getMarkerUrl_ = function() {
    return this.object_.isSelected() ?
      this.object_.getSelectedUrl() :
      this.object_.getUrl();
  };


  /**
   * @method bindMarkerAttributes_
   * @private
   */
  MarkerStrategy.prototype.bindMarkerAttributes_ = function() {
    var iconChangeEvents = [
      'change:url',
      'change:offsetX',
      'change:offsetY',
      'change:selectedUrl',
      'change:selectedOffsetX',
      'change:selectedOffsetY',
      'change:selected'
    ];
    var objectEvents = {
      'change:position': function() {
        var latLng = mapUtil.toLeafletLatLng(this.object_.getPosition());
        this.view_.setLatLng(latLng);
      }
    };

    objectEvents[iconChangeEvents.join(' ')] = function() {
      this.view_.setIcon(this.createIcon_());
    };

    this.listenTo(this.object_, objectEvents);

    this.view_.addEventListener({
      'move dragend': function() {
        var latLon = mapUtil.toAerisLatLon(this.view_.getLatLng());
        this.object_.setPosition(latLon);
      }
    }, this);
  };


  /**
   * @method proxyMarkerEvents_
   * @private
   */
  MarkerStrategy.prototype.proxyMarkerEvents_ = function() {
    this.view_.addEventListener({
      click: function(evt) {
        var latLon = mapUtil.toAerisLatLon(evt.latlng);
        this.object_.trigger('click', latLon, this.object_);
      },
      dragend: function() {
        // Leaflet dragend evt does not provide latLng
        var latLon = this.object_.getPosition();
        this.object_.trigger('dragend', latLon, this.object_);
      }
    }, this);
  };


  return MarkerStrategy;
});

define('aeris/maps/markers/marker',[
  'aeris/util',
  'aeris/maps/extensions/mapextensionobject',
  'aeris/togglebehavior',
  'aeris/errors/validationerror',
  'aeris/maps/strategy/markers/marker'
], function(_, MapExtensionObject, ToggleBehavior, ValidationError, MarkerStrategy) {
  /**
   * A marked location on a map.
   *
   * A Marker is a type of {aeris.ViewModel}, which means that it can bind its attributes to a data model ({aeris.Model} or {Backbone.Model}). This allows you to easily bind data from an API to a marker, or {aeris.maps.markercollections.MarkerCollection}.
   *
   * For example, say you have a data model called `Place`, which receives data from an API like so:
   *
   * <code class="example">
   *    var place = new Place();
   *    place.fetch();
   *    //...
   *    place.toJSON === {
   *      id: 1,
   *      description: 'Joe\'s bar and grill.',
   *      category: 'restaurant',
   *      location: {
   *        lat: 45.23,
   *        long: -90.87
   *      }
   *    }
   * </code>
   *
   * You can now bind a {aeris.maps.Marker} to the place data:
   *
   * <code class="example">
   *    var placeMarker = new aeris.maps.Marker(null, {
   *      data: place,
   *
   *      // Use attribute transforms to translate raw data
   *      // into marker attributes.
   *      // Any changes to the Place model will be reflected
   *      // in the placeMarker, using these attributeTransforms.
   *      attributeTransforms: {
   *
   *        // Format position as [lat, lon]
   *        position: function() {
   *          return [
   *            this.getDataAttribute('location.lat'),
   *            this.getDataAttribute('location.long')
   *          ];
   *        },
   *
   *        // Use data description as marker title
   *        title: function() {
   *          return this.getDataAttribute('description');
   *        },
   *
   *        // Choose a icon url based on the
   *        // data category
   *        url: function() {
   *          var category = this.getDataAttribute('category');
   *
   *          if (category === 'restaurant') {
   *            return 'restaurant_icon.png';
   *          }
   *          else {
   *            return 'some_other_place_icon.png'
   *          }
   *        }
   *      }
   *    });
   * </code>
   *
   * @publicApi
   * @class aeris.maps.markers.Marker
   *
   * @extends aeris.maps.extensions.MapExtensionObject
   * @uses aeris.maps.ToggleBehavior
   * @publicApi
   *
   * @constructor
   *
   * @param {Object=} opt_attrs
   * @param {aeris.maps.LatLon} opt_attrs.position The lat/lon position to set the Marker.
   * @param {Boolean=} opt_attrs.clickable Whether the user can click the marker. Default is true.
   * @param {Boolean=} opt_attrs.draggable Whether the user can drag the marker. Default is true.
   * @param {string=} opt_attrs.url URL to the icon.
   * @param {number=} opt_attrs.width Width of the icon, in pixels.
   * @param {number=} opt_attrs.height Height of the icon, in pixels.
   *
   * @param {Object=} opt_options
   * @param {aeris.maps.AbstractStrategy} opt_options.strategy
   */
  var Marker = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      strategy: MarkerStrategy
    });

    var attrs = _.defaults(opt_attrs || {}, {
      /**
       * @attribute position
       * @type {aeris.maps.LatLon}
       */
      position: [45, -90],

      /**
       * Icon url.
       *
       * @attribute url
       * @type {string}
       */
      url: '//iwx.aerisapi.com/v1.6/wnmapapp/css/assets/markers/earthquake/quake_mini.png',

      /**
       * Pixels between the marker's lat/lon
       * position and the left side of the icon image.
       *
       * @attribute offsetX
       * @type {number}
       */
      offsetX: 9,

      /**
       * Pixels between the marker's lat/lon
       * position and the top of the icon image.
       *
       * @attribute offsetY
       * @type {number}
       */
      offsetY: 9,

      /**
       * Whether to allow click events
       * on the marker.
       *
       * @attribute clickable
       * @type {Boolean}
       */
      clickable: true,

      /**
       * Whether to allow drag events
       * on the marker.
       *
       * @attribute draggable
       * @type {Boolean}
       */
      draggable: false,

      /**
       * Marker title.
       *
       * @attribute title
       * @type {string}
       * @default ''
       */
      title: ''
    });

    // Default selected styles to base styles
    attrs.selectedUrl || (attrs.selectedUrl = attrs.url);
    attrs.selectedOffsetX || (attrs.selectedOffsetX = attrs.offsetX);
    attrs.selectedOffsetY || (attrs.selectedOffsetY = attrs.offsetY);

    MapExtensionObject.call(this, attrs, options);
    ToggleBehavior.call(this);
  };
  _.inherits(Marker, MapExtensionObject);
  _.extend(Marker.prototype, ToggleBehavior.prototype);


  /**
   * @method validate
   * @protected
   */
  Marker.prototype.validate = function(attrs) {
    if (
      attrs.position && (
        !_.isArray(attrs.position) || !_.isNumber(attrs.position[0]) || !_.isNumber(attrs.position[1]) ||
        attrs.position.length !== 2
      )
    ) {
      return new ValidationError(attrs.position.toString() + ' is not a valid latLon position');
    }
    if (!_.isString(attrs.title)) {
      return new ValidationError(attrs.title + ' is not a valid marker title.');
    }
  };


  /**
   * @param {aeris.maps.LatLon} latLon
   * @method setPosition
   */
  Marker.prototype.setPosition = function(latLon) {
    this.set('position', latLon, { validate: true });
  };


  /**
   * @return {aeris.maps.LatLon}
   * @method getPosition
   */
  Marker.prototype.getPosition = function() {
    return this.get('position');
  };


  /**
   * @param {string} url
   * @method setUrl
   */
  Marker.prototype.setUrl = function(url) {
    this.set('url', url, { validate: true });
  };


  /**
   * Return the url of the marker icon.
   *
   * @method getUrl
   * @return {string}
  */
  Marker.prototype.getUrl = function() {
    return this.get('url');
  };

  /**
   * @method setSelectedUrl
   * @param {string} selectedUrl
   */
  Marker.prototype.setSelectedUrl = function(selectedUrl) {
    this.set('selectedUrl', selectedUrl, { validate: true });
  };

  /**
   * @method getSelectedUrl
   * @return {string}
   */
  Marker.prototype.getSelectedUrl = function() {
    return this.get('selectedUrl');
  };


  /**
   * This method method may be overriden to return
   * an arbitrary "type" category for the marker.
   * Used by MarkerClusterer strategies to split up
   * a single MarkerColection into several cluster sets.
   *
   * @method getType
   * @return {?string}
   */
  Marker.prototype.getType = function() {
    return null;
  };


  return _.expose(Marker, 'aeris.maps.markers.Marker');
});

define('aeris/collection',[
  'aeris/util',
  'aeris/model',
  'aeris/events',
  'backbone',
  'aeris/errors/invalidargumenterror'
], function(_, Model, Events, Backbone, InvalidArgumentError) {
  /**
   * Base collection class.
   *
   * @class aeris.Collection
   * @extends Backbone.Collection
   *
   * @param {Array.<aeris.Model>=} opt_models
   * @param {Object} opt_options
   * @param {Boolean=} opt_options.validate
   *        If set to true, will validate all models on instantiation.
   * @constructor
   */
  var Collection = function(opt_models, opt_options) {
    var options = _.extend({
      model: Model,
      validate: false,
      modelOptions: {}
    }, opt_options);

    if (opt_models && !_.isArray(opt_models)) {
      throw new InvalidArgumentError(opt_models + ' is not a valid array of models.');
    }


    /**
     * Options to pass on to
     * models created by this collection.
     *
     * @type {Object}
     * @protected
     * @property modelOptions_
     */
    this.modelOptions_ = options.modelOptions;


    Backbone.Collection.call(this, opt_models, _.pick(options, ['model', 'comparator']));
    Events.call(this);

    if (options.validate) {
      this.isValid();
    }


    /**
     * When any child model's attribute changes
     * @event change
     * @param {aeris.Model} model
     */

    /**
     * When any child model's attribute changes,
     * where [attribute] is the name of the attribute.
     *
     * @event change:[attribute]
     * @param {aeris.Model} model
     * @param {*} value
     */

    /**
     * When a model is added to the
     * {aeris.Collection}.
     *
     * @event add
     * @param {aeris.Model} model
     * @param {aeris.Collection} collection
     */

    /**
     * When a model is removed from the {aeris.Collection}.
     *
     * @event remove
     * @param {aeris.Model} model
     * @param {aeris.Collection} collection
     */
  };
  _.inherits(Collection, Backbone.Collection);
  _.extend(Collection.prototype, Events.prototype);


  /**
   * Runs validation on all collection models.
   *
   * @return {Boolean=} Returns false if any model fails validation.
   * @method isValid
   */
  Collection.prototype.isValid = function() {
    var isValid = true;
    this.each(function(model) {
      if (!model.isValid()) {
        isValid = false;
      }
    });

    return isValid;
  };

  /**
   * Retrieve a model from the collection
   * by id.
   *
   * @param {number|string} id
   * @return {aeris.Model}
   */

  /**
   * Retrieve a model from the collection
   * by index.
   *
   * @method at
   * @param {number} index
   * @return {aeris.Model}
   */

  /**
   * Pass modelOptions on to newly
   * created models.
   *
   * @override
   * @private
   * @method _prepareModel
   */
  Collection.prototype._prepareModel = function(attrs, opt_options) {
    var options = _.defaults(opt_options || {}, this.modelOptions_);

    return Backbone.Collection.prototype._prepareModel.call(this, attrs, options);
  };


  return _.expose(Collection, 'aeris.Collection');
});

/**
 * See http://backbonejs.org/#Collection for full
 * documentation. Provided here to provide documentation
 * for extending classes.
 *
 * @class Backbone.Collection
 */

/**
 * @protected
 * @method _prepareModel
 */

/**
 * @property model
 * @type {Function}
*/

/**
 * Add models to the collection.
 *
 * @method add
 * @param {Array.<Backbone.Model|Object>} models
 */

/**
 * Remove models from the collection.
 *
 * @method remove
 * @param {Array.<Backbone.Model>} models
 */

/**
 * Remove and replace all models in the collection.
 *
 * @method reset
 * @param {Array.<Backbone.Model|Object>=} opt_models
 */

/**
 * @protected
 * @method set
 */

/**
 * @method push
 * @protected
 */

/**
 * @method pop
 * @protected
 */

/**
 * @method shift
 * @protected
 */

/**
 * @method slice
 * @protected
 */

/**
 * The number of models in the collection.
 *
 * @property length
 */

/**
 * @protected
 * @method parse
 */
;
define('aeris/viewcollection',[
  'aeris/util',
  'aeris/collection',
  'aeris/viewmodel'
], function(_, Collection, ViewModel) {
  /**
   * A representation of a data collection, which has been
   * reshaped into a form expected by a view.
   *
   * @class aeris.ViewCollection
   * @extends aeris.Collection
   *
   * @constructor
   * @override
   */
  var ViewCollection = function(opt_models, opt_options) {
    var isDataCollectionProvided = opt_options && opt_options.data;
    var options = _.defaults(opt_options || {}, {
      data: new Collection(),
      model: ViewModel,
      modelOptions: {}
    });

    // Pass attributeTransforms down to the model.
    if (options.attributeTransforms) {
      options.modelOptions.attributeTransforms = options.attributeTransforms;
    }


    /**
     * Data collection.
     *
     * @type {aeris.Collection}
     * @private
     * @property data_
     */
    this.data_ = options.data;


    /**
     * A cache of created view model instances,
     * referenced by their associated data model cid.
     *
     * @type {Object.<string,aeris.ViewModel>}
     * @private
     * @property viewModelLookup_
     */
    this.viewModelLookup_ = {};


    Collection.call(this, opt_models, options);

    /**
     * The bound data collection has made an API request.
     *
     * @event data:request
     * @param {aeris.ViewCollection} viewCollection
     * @param {aeris.Promise} promiseToSync Resolves with raw API response data.
     */
    /**
     * The data API has responded to a request, and the view collection's
     * bound data object has been updated with fetched data.
     *
     * @event data:sync
     * @param {aeris.ViewCollection} viewCollection
     * @param {Object} responseData Raw API response data.
     */

    this.bindToDataCollection_();

    // If no data collection is specified in ctor,
    // do not touch our models.
    if (isDataCollectionProvided) {
      this.updateModelsFromData_();
    }
  };
  _.inherits(ViewCollection, Collection);


  /**
   * @method bindToDataCollection_
   * @private
   */
  ViewCollection.prototype.bindToDataCollection_ = function() {
    this.listenTo(this.data_, {
      add: this.addViewModel_,
      remove: this.removeViewModel_,
      reset: this.updateModelsFromData_
    });

    this.proxyDataSyncEvents_();
  };


  /**
   * @method proxyDataSyncEvents_
   * @private
   */
  ViewCollection.prototype.proxyDataSyncEvents_ = function() {
    this.listenTo(this.data_, {
      request: function(dataObj, promiseToSync, requestOptions) {
        this.trigger('data:request', this, promiseToSync);
      },
      sync: function(dataObj, responseData, requestOptions) {
        this.trigger('data:sync', this, responseData);
      }
    });
  };


  /**
   * Add a view model.
   *
   * @param {aeris.Model} dataModel The data model to associate with the view model.
   * @private
   * @method addViewModel_
   */
  ViewCollection.prototype.addViewModel_ = function(dataModel) {
    var viewModel = this.createViewModel_(dataModel);

    this.viewModelLookup_[dataModel.cid] = viewModel;

    this.add(viewModel);
  };


  /**
   * Remove a view model.
   *
   * @param {aeris.Model} dataModel The associated data model.
   * @private
   * @method removeViewModel_
   */
  ViewCollection.prototype.removeViewModel_ = function(dataModel) {
    var viewModel = this.viewModelLookup_[dataModel.cid];

    // No ViewModel is associated with
    // this data model.
    if (!viewModel) {
      return;
    }

    // Remove the view model
    this.remove(viewModel);
    delete this.viewModelLookup_[dataModel.cid];
  };


  /**
   * Reset view models, to sync up
   * with our data model.
   *
   * @private
   * @method updateModelsFromData_
   */
  ViewCollection.prototype.updateModelsFromData_ = function() {
    var viewModels = [];

    // Reset the lookup
    this.viewModelLookup_ = {};

    // Generate view models
    this.data_.each(function(dataModel) {
      var viewModel = this.createViewModel_(dataModel);

      this.viewModelLookup_[dataModel.cid] = viewModel;

      viewModels.push(viewModel);
    }, this);

    // Reset the view collection
    this.reset(viewModels);
  };


  /**
   * Create a view model, associated with
   * a specified data model.
   *
   * @param {aeris.Model} dataModel
   * @return {aeris.ViewModel}
   *
   * @protected
   * @override
   * @method createViewModel_
   */
  ViewCollection.prototype.createViewModel_ = function(dataModel) {
    return this._prepareModel(undefined, _.defaults({}, this.modelOptions_, {
      data: dataModel
    }));
  };


  /**
   * @return {aeris.Collection}
   * @method getData
   */
  ViewCollection.prototype.getData = function() {
    return this.data_;
  };


  /**
   * @param {Object=} opt_options Options to pass to aeris.Model#fetch.
   * @return {aeris.Promise}
   * @method fetchData
   */
  ViewCollection.prototype.fetchData = function(opt_options) {
    return this.data_.fetch(opt_options);
  };


  return ViewCollection;
});

define('aeris/maps/extensions/mapobjectcollection',[
  'aeris/util',
  'aeris/viewcollection'
], function(_, ViewCollection) {
  /**
   * A collection of {aeris.maps.MapObjectInterface} objects.
   *
   * @class aeris.maps.extensions.MapObjectCollection
   * @extends aeris.ViewCollection
   *
   * @implements aeris.maps.MapObjectInterface
   *
   * @constructor
   * @override
   */
  var MapObjectCollection = function() {
    /**
     * @property map_
     * @private
     * @type {?aeris.maps.Map}
     */
    this.map_ = null;

    ViewCollection.apply(this, arguments);

    this.bindChildrenToMap_();
  };
  _.inherits(MapObjectCollection, ViewCollection);


  MapObjectCollection.prototype.bindChildrenToMap_ = function() {
    this.listenTo(this, {
      add: function(model) {
        model.setMap(this.map_);
      },
      remove: function(model) {
        model.setMap(null);
      },
      reset: function(collection, options) {
        options.previousModels.forEach(function(model) {
          model.setMap(null);
        });
        this.each(function(model) {
          model.setMap(this.getMap());
        }, this);
      }
    });
  };


  /**
   * Set the map on all child MapObjects.
   *
   * Any newly created map objects will be
   * instantiated with the map set here.
   *
   * @override
   * @method setMap
   */
  MapObjectCollection.prototype.setMap = function(map, opt_options) {
    var options = opt_options || {};
    var topic = map ? 'map:set' : 'map:remove';
    var isSameMapAsCurrentlySet = (map === this.map_);

    this.map_ = map;

    this.invoke('setMap', map, options);

    if (!isSameMapAsCurrentlySet && !options.silent) {
      this.trigger(topic, this, map);
    }
  };


  /**
   * @method getMap
   */
  MapObjectCollection.prototype.getMap = function() {
    return this.map_;
  };


  /**
   * @override
   * @return {boolean}
   * @method hasMap
   */
  MapObjectCollection.prototype.hasMap = function() {
    return !!this.map_;
  };

  /**
   * From Backbone.Collection#_onModelEvent
   * @override
   * @method _onModelEvent
   */
  MapObjectCollection.prototype._onModelEvent = function(event, model, collection, options) {
    // Avoid bubbling 'map:set' and 'map:remove' events,
    // So that we do not get multiple events when the
    // collection's map is set.
    var isMapSetEvent = (event === 'map:set') || (event === 'map:remove');
    if (isMapSetEvent) { return; }

    ViewCollection.prototype._onModelEvent.apply(this, arguments);
  };


  return MapObjectCollection;
});

define('aeris/togglecollectionbehavior',[
  'aeris/util'
], function(_) {
  /**
   * A collection of {aeris.ToggleBehavior} models.
   *
   * Should only be mixed into {aeris.Collection} objects.
   *
   * @class aeris.ToggleCollectionBehavior
   * @extensionfor {aeris.Collection}
   *
   * @constructor
   */
  var ToggleCollectionBehavior = function() {
  };

  /**
   * Select all models in the collection.
   *
   * @method selectAll
  */
  ToggleCollectionBehavior.prototype.selectAll = function() {
    this.invoke('select');
  };

  /**
   * Deselect all models in the collection.
   *
   * @method deselectAll
  */
  ToggleCollectionBehavior.prototype.deselectAll = function() {
    this.invoke('deselect');
  };


  /**
   * Toggle whether each model in the collection is selected.
   *
   * @method toggleAll
  */
  ToggleCollectionBehavior.prototype.toggleAll = function() {
    this.invoke('toggle');
  };


  /**
   * Selects the specified model,
   * and deselects all others in the collection.
   *
   * @method selectOnly
   * @param {aeris.Model} modelToSelect
  */
  ToggleCollectionBehavior.prototype.selectOnly = function(modelToSelect) {
    // Note that deselecting all models
    // (including the modelToSelect)
    // would cause an extra event to fire on
    // the modelToSelect, in the case that
    // it is already selected.
    this.each(function(model) {
      if (model !== modelToSelect) {
        model.deselect();
      }
    });

    modelToSelect.select();
  };


  /**
   * @method getSelected
   * @return {Array.<aeris.Model>}
  */
  ToggleCollectionBehavior.prototype.getSelected = function() {
    return this.filter(function(model) {
      return model.isSelected();
    }, this);
  };


  /**
   * @method getDeselected
   * @return {Array.<aeris.Model>}
  */
  ToggleCollectionBehavior.prototype.getDeselected = function() {
    return this.filter(function(model) {
      return !model.isSelected();
    }, this);
  };


  return ToggleCollectionBehavior;
});

define('aeris/config',[
  'module',
  // Using vendor modules to avoid circular dependencies
  // as much as possible
  'backbone',
  'aeris/util'
], function(module, Backbone, _) {
  /**
   * Global configuration object for Aeris.js library.
   *
   * @class aeris.config
   * @extends aeris.Model
   * @publicApi
   *
   * @static
   */
  var Config = function(opt_attrs, opt_options) {
    /**
     * The map type strategy used to
     * load in map rendering components.
     *
     * @attribute strategy
     * @type {string}
     */
    /**
     * Aeris API id.
     *
     * @attribute apiId
     * @type {string}
     */
    /**
     * Aeris API secret.
     *
     * @attribute apiSecret
     * @type {string}
     */
    /**
     * Base url
     * @attribute assetPath
     * @type {string}
     */
    var attrs = _.defaults(opt_attrs || {}, {
      assetPath: '//cdn.aerisjs.com/assets/'
    });

    Backbone.Model.call(this, attrs, opt_options);


    // Bind config strategy to require
    // strategy path
    this.on('change:strategy', function() {
      require.config({
        map: {
          '*': {
            'aeris/maps/strategy': this.get('strategy')
          }
        }
      });
    }, this);
  };
  _.inherits(Config, Backbone.Model);


  /**
   * @method validate
   */
  Config.prototype.validate = function(attrs) {
    if (attrs.strategy && ['gmaps', 'openlayers'].indexOf(attrs.strategy) === -1) {
      throw new Error('Invalid map type strategy. Valid strategies are  ' +
        '\'gmaps\' or \'openlayers\'');
    }
  };


  /**
   * @param {string} strategy
   * @throws {Error} If no strategy is defined, or if
   *                the strategy is not valid.
   * @method setStrategy_
   * @private
   */
  Config.prototype.setStrategy_ = function(strategy) {
    if (!strategy) {
      throw new Error('Unable to set map type strategy: no strategy defined');
    }
    this.set('strategy', strategy, { validate: true });
  };


  /**
   * @method setApiId
   * @param {string} apiId
   */
  Config.prototype.setApiId = function(apiId) {
    this.set('apiId', apiId, { validate: true });
  };

  /**
   * @method setApiSecret
   * @param {string} setApiSecret
   */
  Config.prototype.setApiSecret = function(apiSecret) {
    this.set('apiSecret', apiSecret, { validate: true });
  };

  // Return a singleton config object,
  // propagated with any data from ReqJS's
  // config['aeris/config'] configuration.
  return _.expose(new Config(module.config()), 'aeris.config');
});

define('aeris/maps/markers/config/iconlookup',[
  'aeris/util',
  'aeris/config'
], function(_, config) {
  /**
   * Lookup objects to match a
   * marker type to its icon file name.
   *
   * @property aeris.maps.markers.config.iconLookup
   * @static
   */
  var stormReportDefaults = {
    offsetX: 12,
    offsetY: 11,
    width: 25,
    height: 25
  };
  var lightningDefaults = {
    offsetX: 8,
    offsetY: 17,
    width: 15,
    height: 34,
    anchorText: [-17, 10]
  };

  function stormRepStyles(styles) {
    var stormReportMarkerDefaults = {
      offsetX: 12,
      offsetY: 11,
      width: 25,
      height: 25
    };
    return _.extend(stormReportMarkerDefaults, styles);
  }

  function lightningStyles(styles) {

  }

  return {
    stormReport: {
      avalanche: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_avalanche.png'
      }, stormReportDefaults),
      blizzard: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_snow.png'
      }, stormReportDefaults),
      sleet: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_ice.png'
      }, stormReportDefaults),
      flood: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_flood.png'
      }, stormReportDefaults),
      fog: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_densefog.png'
      }, stormReportDefaults),
      ice: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_ice.png'
      }, stormReportDefaults),
      hail: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_hail.png'
      }, stormReportDefaults),
      lightning: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_lightning.png'
      }, stormReportDefaults),
      rain: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_rain.png'
      }, stormReportDefaults),
      snow: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_snow.png'
      }, stormReportDefaults),
      tides: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_highsurf.png'
      }, stormReportDefaults),
      spout: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_tornado.png'
      }, stormReportDefaults),
      tornado: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_tornado.png'
      }, stormReportDefaults),
      // as in, funnel cloud
      funnel: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_tornado.png'
      }, stormReportDefaults),
      wind: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_highwind.png'
      }, stormReportDefaults),
      downburst: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_highwind.png'
      }, stormReportDefaults),
      winds: _.defaults({
        url: config.get('assetPath') + 'stormrep_marker_highwind.png'
      }, stormReportDefaults)
    },
    earthquake: {
      mini: {
        url: config.get('assetPath') + 'quake_mini.png',
        offsetX: 8,
        offsetY: 9,
        width: 18,
        height: 18,
        anchorText: [-9, 12]
      },
      shallow: {
        url: config.get('assetPath') + 'quake_mini.png',
        offsetX: 8,
        offsetY: 9,
        width: 18,
        height: 18,
        anchorText: [-9, 12]
      },
      minor: {
        url: config.get('assetPath') + 'quake_minor.png',
        offsetX: 14,
        offsetY: 15,
        width: 31,
        height: 31,
        anchorText: [-16, 18]
      },
      light: {
        url: config.get('assetPath') + 'quake_light.png',
        offsetX: 21,
        offsetY: 22,
        width: 45,
        height: 44
      },
      moderate: {
        url: config.get('assetPath') + 'quake_moderate.png',
        offsetX: 28,
        offsetY: 29,
        width: 58,
        height: 58
      },
      strong: {
        url: config.get('assetPath') + 'quake_strong.png',
        offsetX: 42,
        offsetY: 43,
        width: 86,
        height: 86
      },
      major: {
        url: config.get('assetPath') + 'quake_major.png',
        offsetX: 49,
        offsetY: 50,
        width: 100,
        height: 100
      },
      great: {
        url: config.get('assetPath') + 'quake_great.png',
        offsetX: 49,
        offsetY: 50,
        width: 100,
        height: 100
      }
    },
    lightning: {
      // by how old the lightning report is,
      // in minutes.
      // Up to 15 minutes old
      15: _.defaults({
        url: config.get('assetPath') + 'lightning_white.png'
      }, lightningDefaults),
      // Up to 30 minutes old
      30: _.defaults({
        url: config.get('assetPath') + 'lightning_yellow.png'
      }, lightningDefaults),
      // Up to 45 minutes old
      45: _.defaults({
        url: config.get('assetPath') + 'lightning_red.png'
      }, lightningDefaults),
      // Up to 60 minutes old
      60: _.defaults({
        url: config.get('assetPath') + 'lightning_orange.png'
      }, lightningDefaults),
      // Up to 99999 minutes old (catch-all)
      99999: _.defaults({
        url: config.get('assetPath') + 'lightning_blue.png'
      }, lightningDefaults)
    },
    fire: {
      defaultStyles: {
        url: config.get('assetPath') + 'map_fire_marker.png',
        offsetX: 13,
        offsetY: 33,
        width: 27,
        height: 38,
        anchorText: [-19, 16]
      }
    }
  };
});


define('aeris/maps/markercollections/config/clusterstyles',[
  'aeris/util',
  'aeris/config',
  'aeris/maps/markers/config/iconlookup'
], function(_, config, markerIconLookup) {
  /**
   * Styles configuration for marker clusters.
   *
   * See http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/docs/reference.html
   *  "class ClusterIconStyle" for acceptable style options.
   *
   * @class aeris.maps.markercollections.config.clusterStyles
   * @static
   */
  var clusterStyles = {};

  _.each(markerIconLookup, function(markerClassStyles, markerClassName) {
    clusterStyles[markerClassName] = clusterStylesFromMarkerStyles(markerClassStyles);
  });

  clusterStyles.defaultStyles = _.map([
    'marker_green.png',
    'marker_grey.png',
    'marker_yellow.png'
  ], function(markerUrl) {
    return {
      url: config.get('assetPath') + markerUrl,
      width: 18,
      height: 18,
      offsetX: 9,
      offsetY: 9,
      textColor: '#ffffff',
      textSize: 13,
      anchorText: [-14, 15]
    };
  });

  return clusterStyles;


  /**
   * Convert marker styles to markerCluster styles.
   * Basically, just wraps a array around the styles --
   * our MarkerClusterers expect an array of styles, to be used
   * depending on the cluster count. But we're currently using the
   * same icon, no matter what the count.
   *
   markerStyles: {
      typeA: {
        url: '',
        offsetX: 12,
        offsetY: 34
      },
      typeB: {...}
   }

   clusterStyles: {
     typeA: [{
        url: '',
        offsetX: 12,
        offsetY: 34
     }],
     typeB: [{...}]
   }

   */
  function clusterStylesFromMarkerStyles(markerStyles) {
    var clusterStyles = _.clone(markerStyles);
    var defaultClusterStyles = {
      textColor: '#ffffff',
      textSize: 13,
      anchorText: [-14, 15]
    };

    // Wrap type configs in array
    _.each(clusterStyles, function(typeConfig, typeName) {
      clusterStyles[typeName] = [_.defaults({}, typeConfig, defaultClusterStyles)];
    });

    return clusterStyles;
  }
});

/*
 Leaflet.markercluster, Provides Beautiful Animated Marker Clustering functionality for Leaflet, a JS library for interactive maps.
 https://github.com/Leaflet/Leaflet.markercluster
 (c) 2012-2013, Dave Leaver, smartrak
*/
(function (window, document, undefined) {
/*
 * L.MarkerClusterGroup extends L.FeatureGroup by clustering the markers contained within
 */

L.MarkerClusterGroup = L.FeatureGroup.extend({

	options: {
		maxClusterRadius: 80, //A cluster will cover at most this many pixels from its center
		iconCreateFunction: null,

		spiderfyOnMaxZoom: true,
		showCoverageOnHover: true,
		zoomToBoundsOnClick: true,
		singleMarkerMode: false,

		disableClusteringAtZoom: null,

		// Setting this to false prevents the removal of any clusters outside of the viewpoint, which
		// is the default behaviour for performance reasons.
		removeOutsideVisibleBounds: true,

		//Whether to animate adding markers after adding the MarkerClusterGroup to the map
		// If you are adding individual markers set to true, if adding bulk markers leave false for massive performance gains.
		animateAddingMarkers: false,

		//Increase to increase the distance away that spiderfied markers appear from the center
		spiderfyDistanceMultiplier: 1,

		//Options to pass to the L.Polygon constructor
		polygonOptions: {}
	},

	initialize: function (options) {
		L.Util.setOptions(this, options);
		if (!this.options.iconCreateFunction) {
			this.options.iconCreateFunction = this._defaultIconCreateFunction;
		}

		this._featureGroup = L.featureGroup();
		this._featureGroup.on(L.FeatureGroup.EVENTS, this._propagateEvent, this);

		this._nonPointGroup = L.featureGroup();
		this._nonPointGroup.on(L.FeatureGroup.EVENTS, this._propagateEvent, this);

		this._inZoomAnimation = 0;
		this._needsClustering = [];
		this._needsRemoving = []; //Markers removed while we aren't on the map need to be kept track of
		//The bounds of the currently shown area (from _getExpandedVisibleBounds) Updated on zoom/move
		this._currentShownBounds = null;

		this._queue = [];
	},

	addLayer: function (layer) {

		if (layer instanceof L.LayerGroup) {
			var array = [];
			for (var i in layer._layers) {
				array.push(layer._layers[i]);
			}
			return this.addLayers(array);
		}

		//Don't cluster non point data
		if (!layer.getLatLng) {
			this._nonPointGroup.addLayer(layer);
			return this;
		}

		if (!this._map) {
			this._needsClustering.push(layer);
			return this;
		}

		if (this.hasLayer(layer)) {
			return this;
		}


		//If we have already clustered we'll need to add this one to a cluster

		if (this._unspiderfy) {
			this._unspiderfy();
		}

		this._addLayer(layer, this._maxZoom);

		//Work out what is visible
		var visibleLayer = layer,
			currentZoom = this._map.getZoom();
		if (layer.__parent) {
			while (visibleLayer.__parent._zoom >= currentZoom) {
				visibleLayer = visibleLayer.__parent;
			}
		}

		if (this._currentShownBounds.contains(visibleLayer.getLatLng())) {
			if (this.options.animateAddingMarkers) {
				this._animationAddLayer(layer, visibleLayer);
			} else {
				this._animationAddLayerNonAnimated(layer, visibleLayer);
			}
		}
		return this;
	},

	removeLayer: function (layer) {

		if (layer instanceof L.LayerGroup)
		{
			var array = [];
			for (var i in layer._layers) {
				array.push(layer._layers[i]);
			}
			return this.removeLayers(array);
		}

		//Non point layers
		if (!layer.getLatLng) {
			this._nonPointGroup.removeLayer(layer);
			return this;
		}

		if (!this._map) {
			if (!this._arraySplice(this._needsClustering, layer) && this.hasLayer(layer)) {
				this._needsRemoving.push(layer);
			}
			return this;
		}

		if (!layer.__parent) {
			return this;
		}

		if (this._unspiderfy) {
			this._unspiderfy();
			this._unspiderfyLayer(layer);
		}

		//Remove the marker from clusters
		this._removeLayer(layer, true);

		if (this._featureGroup.hasLayer(layer)) {
			this._featureGroup.removeLayer(layer);
			if (layer.setOpacity) {
				layer.setOpacity(1);
			}
		}

		return this;
	},

	//Takes an array of markers and adds them in bulk
	addLayers: function (layersArray) {
		var i, l, m,
			onMap = this._map,
			fg = this._featureGroup,
			npg = this._nonPointGroup;

		for (i = 0, l = layersArray.length; i < l; i++) {
			m = layersArray[i];

			//Not point data, can't be clustered
			if (!m.getLatLng) {
				npg.addLayer(m);
				continue;
			}

			if (this.hasLayer(m)) {
				continue;
			}

			if (!onMap) {
				this._needsClustering.push(m);
				continue;
			}

			this._addLayer(m, this._maxZoom);

			//If we just made a cluster of size 2 then we need to remove the other marker from the map (if it is) or we never will
			if (m.__parent) {
				if (m.__parent.getChildCount() === 2) {
					var markers = m.__parent.getAllChildMarkers(),
						otherMarker = markers[0] === m ? markers[1] : markers[0];
					fg.removeLayer(otherMarker);
				}
			}
		}

		if (onMap) {
			//Update the icons of all those visible clusters that were affected
			fg.eachLayer(function (c) {
				if (c instanceof L.MarkerCluster && c._iconNeedsUpdate) {
					c._updateIcon();
				}
			});

			this._topClusterLevel._recursivelyAddChildrenToMap(null, this._zoom, this._currentShownBounds);
		}

		return this;
	},

	//Takes an array of markers and removes them in bulk
	removeLayers: function (layersArray) {
		var i, l, m,
			fg = this._featureGroup,
			npg = this._nonPointGroup;

		if (!this._map) {
			for (i = 0, l = layersArray.length; i < l; i++) {
				m = layersArray[i];
				this._arraySplice(this._needsClustering, m);
				npg.removeLayer(m);
			}
			return this;
		}

		for (i = 0, l = layersArray.length; i < l; i++) {
			m = layersArray[i];

			if (!m.__parent) {
				npg.removeLayer(m);
				continue;
			}

			this._removeLayer(m, true, true);

			if (fg.hasLayer(m)) {
				fg.removeLayer(m);
				if (m.setOpacity) {
					m.setOpacity(1);
				}
			}
		}

		//Fix up the clusters and markers on the map
		this._topClusterLevel._recursivelyAddChildrenToMap(null, this._zoom, this._currentShownBounds);

		fg.eachLayer(function (c) {
			if (c instanceof L.MarkerCluster) {
				c._updateIcon();
			}
		});

		return this;
	},

	//Removes all layers from the MarkerClusterGroup
	clearLayers: function () {
		//Need our own special implementation as the LayerGroup one doesn't work for us

		//If we aren't on the map (yet), blow away the markers we know of
		if (!this._map) {
			this._needsClustering = [];
			delete this._gridClusters;
			delete this._gridUnclustered;
		}

		if (this._noanimationUnspiderfy) {
			this._noanimationUnspiderfy();
		}

		//Remove all the visible layers
		this._featureGroup.clearLayers();
		this._nonPointGroup.clearLayers();

		this.eachLayer(function (marker) {
			delete marker.__parent;
		});

		if (this._map) {
			//Reset _topClusterLevel and the DistanceGrids
			this._generateInitialClusters();
		}

		return this;
	},

	//Override FeatureGroup.getBounds as it doesn't work
	getBounds: function () {
		var bounds = new L.LatLngBounds();
		if (this._topClusterLevel) {
			bounds.extend(this._topClusterLevel._bounds);
		} else {
			for (var i = this._needsClustering.length - 1; i >= 0; i--) {
				bounds.extend(this._needsClustering[i].getLatLng());
			}
		}

		bounds.extend(this._nonPointGroup.getBounds());

		return bounds;
	},

	//Overrides LayerGroup.eachLayer
	eachLayer: function (method, context) {
		var markers = this._needsClustering.slice(),
		    i;

		if (this._topClusterLevel) {
			this._topClusterLevel.getAllChildMarkers(markers);
		}

		for (i = markers.length - 1; i >= 0; i--) {
			method.call(context, markers[i]);
		}

		this._nonPointGroup.eachLayer(method, context);
	},

	//Overrides LayerGroup.getLayers
	getLayers: function () {
		var layers = [];
		this.eachLayer(function (l) {
			layers.push(l);
		});
		return layers;
	},

	//Overrides LayerGroup.getLayer, WARNING: Really bad performance
	getLayer: function (id) {
		var result = null;

		this.eachLayer(function (l) {
			if (L.stamp(l) === id) {
				result = l;
			}
		});

		return result;
	},

	//Returns true if the given layer is in this MarkerClusterGroup
	hasLayer: function (layer) {
		if (!layer) {
			return false;
		}

		var i, anArray = this._needsClustering;

		for (i = anArray.length - 1; i >= 0; i--) {
			if (anArray[i] === layer) {
				return true;
			}
		}

		anArray = this._needsRemoving;
		for (i = anArray.length - 1; i >= 0; i--) {
			if (anArray[i] === layer) {
				return false;
			}
		}

		return !!(layer.__parent && layer.__parent._group === this) || this._nonPointGroup.hasLayer(layer);
	},

	//Zoom down to show the given layer (spiderfying if necessary) then calls the callback
	zoomToShowLayer: function (layer, callback) {

		var showMarker = function () {
			if ((layer._icon || layer.__parent._icon) && !this._inZoomAnimation) {
				this._map.off('moveend', showMarker, this);
				this.off('animationend', showMarker, this);

				if (layer._icon) {
					callback();
				} else if (layer.__parent._icon) {
					var afterSpiderfy = function () {
						this.off('spiderfied', afterSpiderfy, this);
						callback();
					};

					this.on('spiderfied', afterSpiderfy, this);
					layer.__parent.spiderfy();
				}
			}
		};

		if (layer._icon && this._map.getBounds().contains(layer.getLatLng())) {
			callback();
		} else if (layer.__parent._zoom < this._map.getZoom()) {
			//Layer should be visible now but isn't on screen, just pan over to it
			this._map.on('moveend', showMarker, this);
			this._map.panTo(layer.getLatLng());
		} else {
			this._map.on('moveend', showMarker, this);
			this.on('animationend', showMarker, this);
			this._map.setView(layer.getLatLng(), layer.__parent._zoom + 1);
			layer.__parent.zoomToBounds();
		}
	},

	//Overrides FeatureGroup.onAdd
	onAdd: function (map) {
		this._map = map;
		var i, l, layer;

		if (!isFinite(this._map.getMaxZoom())) {
			throw "Map has no maxZoom specified";
		}

		this._featureGroup.onAdd(map);
		this._nonPointGroup.onAdd(map);

		if (!this._gridClusters) {
			this._generateInitialClusters();
		}

		for (i = 0, l = this._needsRemoving.length; i < l; i++) {
			layer = this._needsRemoving[i];
			this._removeLayer(layer, true);
		}
		this._needsRemoving = [];

		for (i = 0, l = this._needsClustering.length; i < l; i++) {
			layer = this._needsClustering[i];

			//If the layer doesn't have a getLatLng then we can't cluster it, so add it to our child featureGroup
			if (!layer.getLatLng) {
				this._featureGroup.addLayer(layer);
				continue;
			}


			if (layer.__parent) {
				continue;
			}
			this._addLayer(layer, this._maxZoom);
		}
		this._needsClustering = [];


		this._map.on('zoomend', this._zoomEnd, this);
		this._map.on('moveend', this._moveEnd, this);

		if (this._spiderfierOnAdd) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnAdd();
		}

		this._bindEvents();


		//Actually add our markers to the map:

		//Remember the current zoom level and bounds
		this._zoom = this._map.getZoom();
		this._currentShownBounds = this._getExpandedVisibleBounds();

		//Make things appear on the map
		this._topClusterLevel._recursivelyAddChildrenToMap(null, this._zoom, this._currentShownBounds);
	},

	//Overrides FeatureGroup.onRemove
	onRemove: function (map) {
		map.off('zoomend', this._zoomEnd, this);
		map.off('moveend', this._moveEnd, this);

		this._unbindEvents();

		//In case we are in a cluster animation
		this._map._mapPane.className = this._map._mapPane.className.replace(' leaflet-cluster-anim', '');

		if (this._spiderfierOnRemove) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnRemove();
		}



		//Clean up all the layers we added to the map
		this._hideCoverage();
		this._featureGroup.onRemove(map);
		this._nonPointGroup.onRemove(map);

		this._featureGroup.clearLayers();

		this._map = null;
	},

	getVisibleParent: function (marker) {
		var vMarker = marker;
		while (vMarker && !vMarker._icon) {
			vMarker = vMarker.__parent;
		}
		return vMarker || null;
	},

	//Remove the given object from the given array
	_arraySplice: function (anArray, obj) {
		for (var i = anArray.length - 1; i >= 0; i--) {
			if (anArray[i] === obj) {
				anArray.splice(i, 1);
				return true;
			}
		}
	},

	//Internal function for removing a marker from everything.
	//dontUpdateMap: set to true if you will handle updating the map manually (for bulk functions)
	_removeLayer: function (marker, removeFromDistanceGrid, dontUpdateMap) {
		var gridClusters = this._gridClusters,
			gridUnclustered = this._gridUnclustered,
			fg = this._featureGroup,
			map = this._map;

		//Remove the marker from distance clusters it might be in
		if (removeFromDistanceGrid) {
			for (var z = this._maxZoom; z >= 0; z--) {
				if (!gridUnclustered[z].removeObject(marker, map.project(marker.getLatLng(), z))) {
					break;
				}
			}
		}

		//Work our way up the clusters removing them as we go if required
		var cluster = marker.__parent,
			markers = cluster._markers,
			otherMarker;

		//Remove the marker from the immediate parents marker list
		this._arraySplice(markers, marker);

		while (cluster) {
			cluster._childCount--;

			if (cluster._zoom < 0) {
				//Top level, do nothing
				break;
			} else if (removeFromDistanceGrid && cluster._childCount <= 1) { //Cluster no longer required
				//We need to push the other marker up to the parent
				otherMarker = cluster._markers[0] === marker ? cluster._markers[1] : cluster._markers[0];

				//Update distance grid
				gridClusters[cluster._zoom].removeObject(cluster, map.project(cluster._cLatLng, cluster._zoom));
				gridUnclustered[cluster._zoom].addObject(otherMarker, map.project(otherMarker.getLatLng(), cluster._zoom));

				//Move otherMarker up to parent
				this._arraySplice(cluster.__parent._childClusters, cluster);
				cluster.__parent._markers.push(otherMarker);
				otherMarker.__parent = cluster.__parent;

				if (cluster._icon) {
					//Cluster is currently on the map, need to put the marker on the map instead
					fg.removeLayer(cluster);
					if (!dontUpdateMap) {
						fg.addLayer(otherMarker);
					}
				}
			} else {
				cluster._recalculateBounds();
				if (!dontUpdateMap || !cluster._icon) {
					cluster._updateIcon();
				}
			}

			cluster = cluster.__parent;
		}

		delete marker.__parent;
	},

	_isOrIsParent: function (el, oel) {
		while (oel) {
			if (el === oel) {
				return true;
			}
			oel = oel.parentNode;
		}
		return false;
	},

	_propagateEvent: function (e) {
		if (e.layer instanceof L.MarkerCluster) {
			//Prevent multiple clustermouseover/off events if the icon is made up of stacked divs (Doesn't work in ie <= 8, no relatedTarget)
			if (e.originalEvent && this._isOrIsParent(e.layer._icon, e.originalEvent.relatedTarget)) {
				return;
			}
			e.type = 'cluster' + e.type;
		}

		this.fire(e.type, e);
	},

	//Default functionality
	_defaultIconCreateFunction: function (cluster) {
		var childCount = cluster.getChildCount();

		var c = ' marker-cluster-';
		if (childCount < 10) {
			c += 'small';
		} else if (childCount < 100) {
			c += 'medium';
		} else {
			c += 'large';
		}

		return new L.DivIcon({ html: '<div><span>' + childCount + '</span></div>', className: 'marker-cluster' + c, iconSize: new L.Point(40, 40) });
	},

	_bindEvents: function () {
		var map = this._map,
		    spiderfyOnMaxZoom = this.options.spiderfyOnMaxZoom,
		    showCoverageOnHover = this.options.showCoverageOnHover,
		    zoomToBoundsOnClick = this.options.zoomToBoundsOnClick;

		//Zoom on cluster click or spiderfy if we are at the lowest level
		if (spiderfyOnMaxZoom || zoomToBoundsOnClick) {
			this.on('clusterclick', this._zoomOrSpiderfy, this);
		}

		//Show convex hull (boundary) polygon on mouse over
		if (showCoverageOnHover) {
			this.on('clustermouseover', this._showCoverage, this);
			this.on('clustermouseout', this._hideCoverage, this);
			map.on('zoomend', this._hideCoverage, this);
		}
	},

	_zoomOrSpiderfy: function (e) {
		var map = this._map;
		if (map.getMaxZoom() === map.getZoom()) {
			if (this.options.spiderfyOnMaxZoom) {
				e.layer.spiderfy();
			}
		} else if (this.options.zoomToBoundsOnClick) {
			e.layer.zoomToBounds();
		}

    // Focus the map again for keyboard users.
		if (e.originalEvent && e.originalEvent.keyCode === 13) {
			map._container.focus();
		}
	},

	_showCoverage: function (e) {
		var map = this._map;
		if (this._inZoomAnimation) {
			return;
		}
		if (this._shownPolygon) {
			map.removeLayer(this._shownPolygon);
		}
		if (e.layer.getChildCount() > 2 && e.layer !== this._spiderfied) {
			this._shownPolygon = new L.Polygon(e.layer.getConvexHull(), this.options.polygonOptions);
			map.addLayer(this._shownPolygon);
		}
	},

	_hideCoverage: function () {
		if (this._shownPolygon) {
			this._map.removeLayer(this._shownPolygon);
			this._shownPolygon = null;
		}
	},

	_unbindEvents: function () {
		var spiderfyOnMaxZoom = this.options.spiderfyOnMaxZoom,
			showCoverageOnHover = this.options.showCoverageOnHover,
			zoomToBoundsOnClick = this.options.zoomToBoundsOnClick,
			map = this._map;

		if (spiderfyOnMaxZoom || zoomToBoundsOnClick) {
			this.off('clusterclick', this._zoomOrSpiderfy, this);
		}
		if (showCoverageOnHover) {
			this.off('clustermouseover', this._showCoverage, this);
			this.off('clustermouseout', this._hideCoverage, this);
			map.off('zoomend', this._hideCoverage, this);
		}
	},

	_zoomEnd: function () {
		if (!this._map) { //May have been removed from the map by a zoomEnd handler
			return;
		}
		this._mergeSplitClusters();

		this._zoom = this._map._zoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();
	},

	_moveEnd: function () {
		if (this._inZoomAnimation) {
			return;
		}

		var newBounds = this._getExpandedVisibleBounds();

		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom, newBounds);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, this._map._zoom, newBounds);

		this._currentShownBounds = newBounds;
		return;
	},

	_generateInitialClusters: function () {
		var maxZoom = this._map.getMaxZoom(),
			radius = this.options.maxClusterRadius;

		if (this.options.disableClusteringAtZoom) {
			maxZoom = this.options.disableClusteringAtZoom - 1;
		}
		this._maxZoom = maxZoom;
		this._gridClusters = {};
		this._gridUnclustered = {};

		//Set up DistanceGrids for each zoom
		for (var zoom = maxZoom; zoom >= 0; zoom--) {
			this._gridClusters[zoom] = new L.DistanceGrid(radius);
			this._gridUnclustered[zoom] = new L.DistanceGrid(radius);
		}

		this._topClusterLevel = new L.MarkerCluster(this, -1);
	},

	//Zoom: Zoom to start adding at (Pass this._maxZoom to start at the bottom)
	_addLayer: function (layer, zoom) {
		var gridClusters = this._gridClusters,
		    gridUnclustered = this._gridUnclustered,
		    markerPoint, z;

		if (this.options.singleMarkerMode) {
			layer.options.icon = this.options.iconCreateFunction({
				getChildCount: function () {
					return 1;
				},
				getAllChildMarkers: function () {
					return [layer];
				}
			});
		}

		//Find the lowest zoom level to slot this one in
		for (; zoom >= 0; zoom--) {
			markerPoint = this._map.project(layer.getLatLng(), zoom); // calculate pixel position

			//Try find a cluster close by
			var closest = gridClusters[zoom].getNearObject(markerPoint);
			if (closest) {
				closest._addChild(layer);
				layer.__parent = closest;
				return;
			}

			//Try find a marker close by to form a new cluster with
			closest = gridUnclustered[zoom].getNearObject(markerPoint);
			if (closest) {
				var parent = closest.__parent;
				if (parent) {
					this._removeLayer(closest, false);
				}

				//Create new cluster with these 2 in it

				var newCluster = new L.MarkerCluster(this, zoom, closest, layer);
				gridClusters[zoom].addObject(newCluster, this._map.project(newCluster._cLatLng, zoom));
				closest.__parent = newCluster;
				layer.__parent = newCluster;

				//First create any new intermediate parent clusters that don't exist
				var lastParent = newCluster;
				for (z = zoom - 1; z > parent._zoom; z--) {
					lastParent = new L.MarkerCluster(this, z, lastParent);
					gridClusters[z].addObject(lastParent, this._map.project(closest.getLatLng(), z));
				}
				parent._addChild(lastParent);

				//Remove closest from this zoom level and any above that it is in, replace with newCluster
				for (z = zoom; z >= 0; z--) {
					if (!gridUnclustered[z].removeObject(closest, this._map.project(closest.getLatLng(), z))) {
						break;
					}
				}

				return;
			}

			//Didn't manage to cluster in at this zoom, record us as a marker here and continue upwards
			gridUnclustered[zoom].addObject(layer, markerPoint);
		}

		//Didn't get in anything, add us to the top
		this._topClusterLevel._addChild(layer);
		layer.__parent = this._topClusterLevel;
		return;
	},

	//Enqueue code to fire after the marker expand/contract has happened
	_enqueue: function (fn) {
		this._queue.push(fn);
		if (!this._queueTimeout) {
			this._queueTimeout = setTimeout(L.bind(this._processQueue, this), 300);
		}
	},
	_processQueue: function () {
		for (var i = 0; i < this._queue.length; i++) {
			this._queue[i].call(this);
		}
		this._queue.length = 0;
		clearTimeout(this._queueTimeout);
		this._queueTimeout = null;
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {

		//Incase we are starting to split before the animation finished
		this._processQueue();

		if (this._zoom < this._map._zoom && this._currentShownBounds.contains(this._getExpandedVisibleBounds())) { //Zoom in, split
			this._animationStart();
			//Remove clusters now off screen
			this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom, this._getExpandedVisibleBounds());

			this._animationZoomIn(this._zoom, this._map._zoom);

		} else if (this._zoom > this._map._zoom) { //Zoom out, merge
			this._animationStart();

			this._animationZoomOut(this._zoom, this._map._zoom);
		} else {
			this._moveEnd();
		}
	},

	//Gets the maps visible bounds expanded in each direction by the size of the screen (so the user cannot see an area we do not cover in one pan)
	_getExpandedVisibleBounds: function () {
		if (!this.options.removeOutsideVisibleBounds) {
			return this.getBounds();
		}

		var map = this._map,
			bounds = map.getBounds(),
			sw = bounds._southWest,
			ne = bounds._northEast,
			latDiff = L.Browser.mobile ? 0 : Math.abs(sw.lat - ne.lat),
			lngDiff = L.Browser.mobile ? 0 : Math.abs(sw.lng - ne.lng);

		return new L.LatLngBounds(
			new L.LatLng(sw.lat - latDiff, sw.lng - lngDiff, true),
			new L.LatLng(ne.lat + latDiff, ne.lng + lngDiff, true));
	},

	//Shared animation code
	_animationAddLayerNonAnimated: function (layer, newCluster) {
		if (newCluster === layer) {
			this._featureGroup.addLayer(layer);
		} else if (newCluster._childCount === 2) {
			newCluster._addToMap();

			var markers = newCluster.getAllChildMarkers();
			this._featureGroup.removeLayer(markers[0]);
			this._featureGroup.removeLayer(markers[1]);
		} else {
			newCluster._updateIcon();
		}
	}
});

L.MarkerClusterGroup.include(!L.DomUtil.TRANSITION ? {

	//Non Animated versions of everything
	_animationStart: function () {
		//Do nothing...
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());
	},
	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());
	},
	_animationAddLayer: function (layer, newCluster) {
		this._animationAddLayerNonAnimated(layer, newCluster);
	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
		this._inZoomAnimation++;
	},
	_animationEnd: function () {
		if (this._map) {
			this._map._mapPane.className = this._map._mapPane.className.replace(' leaflet-cluster-anim', '');
		}
		this._inZoomAnimation--;
		this.fire('animationend');
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		var bounds = this._getExpandedVisibleBounds(),
		    fg = this._featureGroup,
		    i;

		//Add all children of current clusters to map and remove those clusters from map
		this._topClusterLevel._recursively(bounds, previousZoomLevel, 0, function (c) {
			var startPos = c._latlng,
				markers = c._markers,
				m;

			if (!bounds.contains(startPos)) {
				startPos = null;
			}

			if (c._isSingleParent() && previousZoomLevel + 1 === newZoomLevel) { //Immediately add the new child and remove us
				fg.removeLayer(c);
				c._recursivelyAddChildrenToMap(null, newZoomLevel, bounds);
			} else {
				//Fade out old cluster
				c.setOpacity(0);
				c._recursivelyAddChildrenToMap(startPos, newZoomLevel, bounds);
			}

			//Remove all markers that aren't visible any more
			//TODO: Do we actually need to do this on the higher levels too?
			for (i = markers.length - 1; i >= 0; i--) {
				m = markers[i];
				if (!bounds.contains(m._latlng)) {
					fg.removeLayer(m);
				}
			}

		});

		this._forceLayout();

		//Update opacities
		this._topClusterLevel._recursivelyBecomeVisible(bounds, newZoomLevel);
		//TODO Maybe? Update markers in _recursivelyBecomeVisible
		fg.eachLayer(function (n) {
			if (!(n instanceof L.MarkerCluster) && n._icon) {
				n.setOpacity(1);
			}
		});

		//update the positions of the just added clusters/markers
		this._topClusterLevel._recursively(bounds, previousZoomLevel, newZoomLevel, function (c) {
			c._recursivelyRestoreChildPositions(newZoomLevel);
		});

		//Remove the old clusters and close the zoom animation
		this._enqueue(function () {
			//update the positions of the just added clusters/markers
			this._topClusterLevel._recursively(bounds, previousZoomLevel, 0, function (c) {
				fg.removeLayer(c);
				c.setOpacity(1);
			});

			this._animationEnd();
		});
	},

	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		this._animationZoomOutSingle(this._topClusterLevel, previousZoomLevel - 1, newZoomLevel);

		//Need to add markers for those that weren't on the map before but are now
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());
		//Remove markers that were on the map before but won't be now
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel, this._getExpandedVisibleBounds());
	},
	_animationZoomOutSingle: function (cluster, previousZoomLevel, newZoomLevel) {
		var bounds = this._getExpandedVisibleBounds();

		//Animate all of the markers in the clusters to move to their cluster center point
		cluster._recursivelyAnimateChildrenInAndAddSelfToMap(bounds, previousZoomLevel + 1, newZoomLevel);

		var me = this;

		//Update the opacity (If we immediately set it they won't animate)
		this._forceLayout();
		cluster._recursivelyBecomeVisible(bounds, newZoomLevel);

		//TODO: Maybe use the transition timing stuff to make this more reliable
		//When the animations are done, tidy up
		this._enqueue(function () {

			//This cluster stopped being a cluster before the timeout fired
			if (cluster._childCount === 1) {
				var m = cluster._markers[0];
				//If we were in a cluster animation at the time then the opacity and position of our child could be wrong now, so fix it
				m.setLatLng(m.getLatLng());
				m.setOpacity(1);
			} else {
				cluster._recursively(bounds, newZoomLevel, 0, function (c) {
					c._recursivelyRemoveChildrenFromMap(bounds, previousZoomLevel + 1);
				});
			}
			me._animationEnd();
		});
	},
	_animationAddLayer: function (layer, newCluster) {
		var me = this,
			fg = this._featureGroup;

		fg.addLayer(layer);
		if (newCluster !== layer) {
			if (newCluster._childCount > 2) { //Was already a cluster

				newCluster._updateIcon();
				this._forceLayout();
				this._animationStart();

				layer._setPos(this._map.latLngToLayerPoint(newCluster.getLatLng()));
				layer.setOpacity(0);

				this._enqueue(function () {
					fg.removeLayer(layer);
					layer.setOpacity(1);

					me._animationEnd();
				});

			} else { //Just became a cluster
				this._forceLayout();

				me._animationStart();
				me._animationZoomOutSingle(newCluster, this._map.getMaxZoom(), this._map.getZoom());
			}
		}
	},

	//Force a browser layout of stuff in the map
	// Should apply the current opacity and location to all elements so we can update them again for an animation
	_forceLayout: function () {
		//In my testing this works, infact offsetWidth of any element seems to work.
		//Could loop all this._layers and do this for each _icon if it stops working

		L.Util.falseFn(document.body.offsetWidth);
	}
});

L.markerClusterGroup = function (options) {
	return new L.MarkerClusterGroup(options);
};


L.MarkerCluster = L.Marker.extend({
	initialize: function (group, zoom, a, b) {

		L.Marker.prototype.initialize.call(this, a ? (a._cLatLng || a.getLatLng()) : new L.LatLng(0, 0), { icon: this });


		this._group = group;
		this._zoom = zoom;

		this._markers = [];
		this._childClusters = [];
		this._childCount = 0;
		this._iconNeedsUpdate = true;

		this._bounds = new L.LatLngBounds();

		if (a) {
			this._addChild(a);
		}
		if (b) {
			this._addChild(b);
		}
	},

	//Recursively retrieve all child markers of this cluster
	getAllChildMarkers: function (storageArray) {
		storageArray = storageArray || [];

		for (var i = this._childClusters.length - 1; i >= 0; i--) {
			this._childClusters[i].getAllChildMarkers(storageArray);
		}

		for (var j = this._markers.length - 1; j >= 0; j--) {
			storageArray.push(this._markers[j]);
		}

		return storageArray;
	},

	//Returns the count of how many child markers we have
	getChildCount: function () {
		return this._childCount;
	},

	//Zoom to the minimum of showing all of the child markers, or the extents of this cluster
	zoomToBounds: function () {
		var childClusters = this._childClusters.slice(),
			map = this._group._map,
			boundsZoom = map.getBoundsZoom(this._bounds),
			zoom = this._zoom + 1,
			mapZoom = map.getZoom(),
			i;

		//calculate how fare we need to zoom down to see all of the markers
		while (childClusters.length > 0 && boundsZoom > zoom) {
			zoom++;
			var newClusters = [];
			for (i = 0; i < childClusters.length; i++) {
				newClusters = newClusters.concat(childClusters[i]._childClusters);
			}
			childClusters = newClusters;
		}

		if (boundsZoom > zoom) {
			this._group._map.setView(this._latlng, zoom);
		} else if (boundsZoom <= mapZoom) { //If fitBounds wouldn't zoom us down, zoom us down instead
			this._group._map.setView(this._latlng, mapZoom + 1);
		} else {
			this._group._map.fitBounds(this._bounds);
		}
	},

	getBounds: function () {
		var bounds = new L.LatLngBounds();
		bounds.extend(this._bounds);
		return bounds;
	},

	_updateIcon: function () {
		this._iconNeedsUpdate = true;
		if (this._icon) {
			this.setIcon(this);
		}
	},

	//Cludge for Icon, we pretend to be an icon for performance
	createIcon: function () {
		if (this._iconNeedsUpdate) {
			this._iconObj = this._group.options.iconCreateFunction(this);
			this._iconNeedsUpdate = false;
		}
		return this._iconObj.createIcon();
	},
	createShadow: function () {
		return this._iconObj.createShadow();
	},


	_addChild: function (new1, isNotificationFromChild) {

		this._iconNeedsUpdate = true;
		this._expandBounds(new1);

		if (new1 instanceof L.MarkerCluster) {
			if (!isNotificationFromChild) {
				this._childClusters.push(new1);
				new1.__parent = this;
			}
			this._childCount += new1._childCount;
		} else {
			if (!isNotificationFromChild) {
				this._markers.push(new1);
			}
			this._childCount++;
		}

		if (this.__parent) {
			this.__parent._addChild(new1, true);
		}
	},

	//Expand our bounds and tell our parent to
	_expandBounds: function (marker) {
		var addedCount,
		    addedLatLng = marker._wLatLng || marker._latlng;

		if (marker instanceof L.MarkerCluster) {
			this._bounds.extend(marker._bounds);
			addedCount = marker._childCount;
		} else {
			this._bounds.extend(addedLatLng);
			addedCount = 1;
		}

		if (!this._cLatLng) {
			// when clustering, take position of the first point as the cluster center
			this._cLatLng = marker._cLatLng || addedLatLng;
		}

		// when showing clusters, take weighted average of all points as cluster center
		var totalCount = this._childCount + addedCount;

		//Calculate weighted latlng for display
		if (!this._wLatLng) {
			this._latlng = this._wLatLng = new L.LatLng(addedLatLng.lat, addedLatLng.lng);
		} else {
			this._wLatLng.lat = (addedLatLng.lat * addedCount + this._wLatLng.lat * this._childCount) / totalCount;
			this._wLatLng.lng = (addedLatLng.lng * addedCount + this._wLatLng.lng * this._childCount) / totalCount;
		}
	},

	//Set our markers position as given and add it to the map
	_addToMap: function (startPos) {
		if (startPos) {
			this._backupLatlng = this._latlng;
			this.setLatLng(startPos);
		}
		this._group._featureGroup.addLayer(this);
	},

	_recursivelyAnimateChildrenIn: function (bounds, center, maxZoom) {
		this._recursively(bounds, 0, maxZoom - 1,
			function (c) {
				var markers = c._markers,
					i, m;
				for (i = markers.length - 1; i >= 0; i--) {
					m = markers[i];

					//Only do it if the icon is still on the map
					if (m._icon) {
						m._setPos(center);
						m.setOpacity(0);
					}
				}
			},
			function (c) {
				var childClusters = c._childClusters,
					j, cm;
				for (j = childClusters.length - 1; j >= 0; j--) {
					cm = childClusters[j];
					if (cm._icon) {
						cm._setPos(center);
						cm.setOpacity(0);
					}
				}
			}
		);
	},

	_recursivelyAnimateChildrenInAndAddSelfToMap: function (bounds, previousZoomLevel, newZoomLevel) {
		this._recursively(bounds, newZoomLevel, 0,
			function (c) {
				c._recursivelyAnimateChildrenIn(bounds, c._group._map.latLngToLayerPoint(c.getLatLng()).round(), previousZoomLevel);

				//TODO: depthToAnimateIn affects _isSingleParent, if there is a multizoom we may/may not be.
				//As a hack we only do a animation free zoom on a single level zoom, if someone does multiple levels then we always animate
				if (c._isSingleParent() && previousZoomLevel - 1 === newZoomLevel) {
					c.setOpacity(1);
					c._recursivelyRemoveChildrenFromMap(bounds, previousZoomLevel); //Immediately remove our children as we are replacing them. TODO previousBounds not bounds
				} else {
					c.setOpacity(0);
				}

				c._addToMap();
			}
		);
	},

	_recursivelyBecomeVisible: function (bounds, zoomLevel) {
		this._recursively(bounds, 0, zoomLevel, null, function (c) {
			c.setOpacity(1);
		});
	},

	_recursivelyAddChildrenToMap: function (startPos, zoomLevel, bounds) {
		this._recursively(bounds, -1, zoomLevel,
			function (c) {
				if (zoomLevel === c._zoom) {
					return;
				}

				//Add our child markers at startPos (so they can be animated out)
				for (var i = c._markers.length - 1; i >= 0; i--) {
					var nm = c._markers[i];

					if (!bounds.contains(nm._latlng)) {
						continue;
					}

					if (startPos) {
						nm._backupLatlng = nm.getLatLng();

						nm.setLatLng(startPos);
						if (nm.setOpacity) {
							nm.setOpacity(0);
						}
					}

					c._group._featureGroup.addLayer(nm);
				}
			},
			function (c) {
				c._addToMap(startPos);
			}
		);
	},

	_recursivelyRestoreChildPositions: function (zoomLevel) {
		//Fix positions of child markers
		for (var i = this._markers.length - 1; i >= 0; i--) {
			var nm = this._markers[i];
			if (nm._backupLatlng) {
				nm.setLatLng(nm._backupLatlng);
				delete nm._backupLatlng;
			}
		}

		if (zoomLevel - 1 === this._zoom) {
			//Reposition child clusters
			for (var j = this._childClusters.length - 1; j >= 0; j--) {
				this._childClusters[j]._restorePosition();
			}
		} else {
			for (var k = this._childClusters.length - 1; k >= 0; k--) {
				this._childClusters[k]._recursivelyRestoreChildPositions(zoomLevel);
			}
		}
	},

	_restorePosition: function () {
		if (this._backupLatlng) {
			this.setLatLng(this._backupLatlng);
			delete this._backupLatlng;
		}
	},

	//exceptBounds: If set, don't remove any markers/clusters in it
	_recursivelyRemoveChildrenFromMap: function (previousBounds, zoomLevel, exceptBounds) {
		var m, i;
		this._recursively(previousBounds, -1, zoomLevel - 1,
			function (c) {
				//Remove markers at every level
				for (i = c._markers.length - 1; i >= 0; i--) {
					m = c._markers[i];
					if (!exceptBounds || !exceptBounds.contains(m._latlng)) {
						c._group._featureGroup.removeLayer(m);
						if (m.setOpacity) {
							m.setOpacity(1);
						}
					}
				}
			},
			function (c) {
				//Remove child clusters at just the bottom level
				for (i = c._childClusters.length - 1; i >= 0; i--) {
					m = c._childClusters[i];
					if (!exceptBounds || !exceptBounds.contains(m._latlng)) {
						c._group._featureGroup.removeLayer(m);
						if (m.setOpacity) {
							m.setOpacity(1);
						}
					}
				}
			}
		);
	},

	//Run the given functions recursively to this and child clusters
	// boundsToApplyTo: a L.LatLngBounds representing the bounds of what clusters to recurse in to
	// zoomLevelToStart: zoom level to start running functions (inclusive)
	// zoomLevelToStop: zoom level to stop running functions (inclusive)
	// runAtEveryLevel: function that takes an L.MarkerCluster as an argument that should be applied on every level
	// runAtBottomLevel: function that takes an L.MarkerCluster as an argument that should be applied at only the bottom level
	_recursively: function (boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel) {
		var childClusters = this._childClusters,
		    zoom = this._zoom,
			i, c;

		if (zoomLevelToStart > zoom) { //Still going down to required depth, just recurse to child clusters
			for (i = childClusters.length - 1; i >= 0; i--) {
				c = childClusters[i];
				if (boundsToApplyTo.intersects(c._bounds)) {
					c._recursively(boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel);
				}
			}
		} else { //In required depth

			if (runAtEveryLevel) {
				runAtEveryLevel(this);
			}
			if (runAtBottomLevel && this._zoom === zoomLevelToStop) {
				runAtBottomLevel(this);
			}

			//TODO: This loop is almost the same as above
			if (zoomLevelToStop > zoom) {
				for (i = childClusters.length - 1; i >= 0; i--) {
					c = childClusters[i];
					if (boundsToApplyTo.intersects(c._bounds)) {
						c._recursively(boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel);
					}
				}
			}
		}
	},

	_recalculateBounds: function () {
		var markers = this._markers,
			childClusters = this._childClusters,
			i;

		this._bounds = new L.LatLngBounds();
		delete this._wLatLng;

		for (i = markers.length - 1; i >= 0; i--) {
			this._expandBounds(markers[i]);
		}
		for (i = childClusters.length - 1; i >= 0; i--) {
			this._expandBounds(childClusters[i]);
		}
	},


	//Returns true if we are the parent of only one cluster and that cluster is the same as us
	_isSingleParent: function () {
		//Don't need to check this._markers as the rest won't work if there are any
		return this._childClusters.length > 0 && this._childClusters[0]._childCount === this._childCount;
	}
});



L.DistanceGrid = function (cellSize) {
	this._cellSize = cellSize;
	this._sqCellSize = cellSize * cellSize;
	this._grid = {};
	this._objectPoint = { };
};

L.DistanceGrid.prototype = {

	addObject: function (obj, point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    grid = this._grid,
		    row = grid[y] = grid[y] || {},
		    cell = row[x] = row[x] || [],
		    stamp = L.Util.stamp(obj);

		this._objectPoint[stamp] = point;

		cell.push(obj);
	},

	updateObject: function (obj, point) {
		this.removeObject(obj);
		this.addObject(obj, point);
	},

	//Returns true if the object was found
	removeObject: function (obj, point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    grid = this._grid,
		    row = grid[y] = grid[y] || {},
		    cell = row[x] = row[x] || [],
		    i, len;

		delete this._objectPoint[L.Util.stamp(obj)];

		for (i = 0, len = cell.length; i < len; i++) {
			if (cell[i] === obj) {

				cell.splice(i, 1);

				if (len === 1) {
					delete row[x];
				}

				return true;
			}
		}

	},

	eachObject: function (fn, context) {
		var i, j, k, len, row, cell, removed,
		    grid = this._grid;

		for (i in grid) {
			row = grid[i];

			for (j in row) {
				cell = row[j];

				for (k = 0, len = cell.length; k < len; k++) {
					removed = fn.call(context, cell[k]);
					if (removed) {
						k--;
						len--;
					}
				}
			}
		}
	},

	getNearObject: function (point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    i, j, k, row, cell, len, obj, dist,
		    objectPoint = this._objectPoint,
		    closestDistSq = this._sqCellSize,
		    closest = null;

		for (i = y - 1; i <= y + 1; i++) {
			row = this._grid[i];
			if (row) {

				for (j = x - 1; j <= x + 1; j++) {
					cell = row[j];
					if (cell) {

						for (k = 0, len = cell.length; k < len; k++) {
							obj = cell[k];
							dist = this._sqDist(objectPoint[L.Util.stamp(obj)], point);
							if (dist < closestDistSq) {
								closestDistSq = dist;
								closest = obj;
							}
						}
					}
				}
			}
		}
		return closest;
	},

	_getCoord: function (x) {
		return Math.floor(x / this._cellSize);
	},

	_sqDist: function (p, p2) {
		var dx = p2.x - p.x,
		    dy = p2.y - p.y;
		return dx * dx + dy * dy;
	}
};


/* Copyright (c) 2012 the authors listed at the following URL, and/or
the authors of referenced articles or incorporated external code:
http://en.literateprograms.org/Quickhull_(Javascript)?action=history&offset=20120410175256

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Retrieved from: http://en.literateprograms.org/Quickhull_(Javascript)?oldid=18434
*/

(function () {
	L.QuickHull = {

		/*
		 * @param {Object} cpt a point to be measured from the baseline
		 * @param {Array} bl the baseline, as represented by a two-element
		 *   array of latlng objects.
		 * @returns {Number} an approximate distance measure
		 */
		getDistant: function (cpt, bl) {
			var vY = bl[1].lat - bl[0].lat,
				vX = bl[0].lng - bl[1].lng;
			return (vX * (cpt.lat - bl[0].lat) + vY * (cpt.lng - bl[0].lng));
		},

		/*
		 * @param {Array} baseLine a two-element array of latlng objects
		 *   representing the baseline to project from
		 * @param {Array} latLngs an array of latlng objects
		 * @returns {Object} the maximum point and all new points to stay
		 *   in consideration for the hull.
		 */
		findMostDistantPointFromBaseLine: function (baseLine, latLngs) {
			var maxD = 0,
				maxPt = null,
				newPoints = [],
				i, pt, d;

			for (i = latLngs.length - 1; i >= 0; i--) {
				pt = latLngs[i];
				d = this.getDistant(pt, baseLine);

				if (d > 0) {
					newPoints.push(pt);
				} else {
					continue;
				}

				if (d > maxD) {
					maxD = d;
					maxPt = pt;
				}
			}

			return { maxPoint: maxPt, newPoints: newPoints };
		},


		/*
		 * Given a baseline, compute the convex hull of latLngs as an array
		 * of latLngs.
		 *
		 * @param {Array} latLngs
		 * @returns {Array}
		 */
		buildConvexHull: function (baseLine, latLngs) {
			var convexHullBaseLines = [],
				t = this.findMostDistantPointFromBaseLine(baseLine, latLngs);

			if (t.maxPoint) { // if there is still a point "outside" the base line
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([baseLine[0], t.maxPoint], t.newPoints)
					);
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([t.maxPoint, baseLine[1]], t.newPoints)
					);
				return convexHullBaseLines;
			} else {  // if there is no more point "outside" the base line, the current base line is part of the convex hull
				return [baseLine[0]];
			}
		},

		/*
		 * Given an array of latlngs, compute a convex hull as an array
		 * of latlngs
		 *
		 * @param {Array} latLngs
		 * @returns {Array}
		 */
		getConvexHull: function (latLngs) {
			// find first baseline
			var maxLat = false, minLat = false,
				maxPt = null, minPt = null,
				i;

			for (i = latLngs.length - 1; i >= 0; i--) {
				var pt = latLngs[i];
				if (maxLat === false || pt.lat > maxLat) {
					maxPt = pt;
					maxLat = pt.lat;
				}
				if (minLat === false || pt.lat < minLat) {
					minPt = pt;
					minLat = pt.lat;
				}
			}
			var ch = [].concat(this.buildConvexHull([minPt, maxPt], latLngs),
								this.buildConvexHull([maxPt, minPt], latLngs));
			return ch;
		}
	};
}());

L.MarkerCluster.include({
	getConvexHull: function () {
		var childMarkers = this.getAllChildMarkers(),
			points = [],
			p, i;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			p = childMarkers[i].getLatLng();
			points.push(p);
		}

		return L.QuickHull.getConvexHull(points);
	}
});


//This code is 100% based on https://github.com/jawj/OverlappingMarkerSpiderfier-Leaflet
//Huge thanks to jawj for implementing it first to make my job easy :-)

L.MarkerCluster.include({

	_2PI: Math.PI * 2,
	_circleFootSeparation: 25, //related to circumference of circle
	_circleStartAngle: Math.PI / 6,

	_spiralFootSeparation:  28, //related to size of spiral (experiment!)
	_spiralLengthStart: 11,
	_spiralLengthFactor: 5,

	_circleSpiralSwitchover: 9, //show spiral instead of circle from this marker count upwards.
								// 0 -> always spiral; Infinity -> always circle

	spiderfy: function () {
		if (this._group._spiderfied === this || this._group._inZoomAnimation) {
			return;
		}

		var childMarkers = this.getAllChildMarkers(),
			group = this._group,
			map = group._map,
			center = map.latLngToLayerPoint(this._latlng),
			positions;

		this._group._unspiderfy();
		this._group._spiderfied = this;

		//TODO Maybe: childMarkers order by distance to center

		if (childMarkers.length >= this._circleSpiralSwitchover) {
			positions = this._generatePointsSpiral(childMarkers.length, center);
		} else {
			center.y += 10; //Otherwise circles look wrong
			positions = this._generatePointsCircle(childMarkers.length, center);
		}

		this._animationSpiderfy(childMarkers, positions);
	},

	unspiderfy: function (zoomDetails) {
		/// <param Name="zoomDetails">Argument from zoomanim if being called in a zoom animation or null otherwise</param>
		if (this._group._inZoomAnimation) {
			return;
		}
		this._animationUnspiderfy(zoomDetails);

		this._group._spiderfied = null;
	},

	_generatePointsCircle: function (count, centerPt) {
		var circumference = this._group.options.spiderfyDistanceMultiplier * this._circleFootSeparation * (2 + count),
			legLength = circumference / this._2PI,  //radius from circumference
			angleStep = this._2PI / count,
			res = [],
			i, angle;

		res.length = count;

		for (i = count - 1; i >= 0; i--) {
			angle = this._circleStartAngle + i * angleStep;
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle))._round();
		}

		return res;
	},

	_generatePointsSpiral: function (count, centerPt) {
		var legLength = this._group.options.spiderfyDistanceMultiplier * this._spiralLengthStart,
			separation = this._group.options.spiderfyDistanceMultiplier * this._spiralFootSeparation,
			lengthFactor = this._group.options.spiderfyDistanceMultiplier * this._spiralLengthFactor,
			angle = 0,
			res = [],
			i;

		res.length = count;

		for (i = count - 1; i >= 0; i--) {
			angle += separation / legLength + i * 0.0005;
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle))._round();
			legLength += this._2PI * lengthFactor / angle;
		}
		return res;
	},

	_noanimationUnspiderfy: function () {
		var group = this._group,
			map = group._map,
			fg = group._featureGroup,
			childMarkers = this.getAllChildMarkers(),
			m, i;

		this.setOpacity(1);
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			fg.removeLayer(m);

			if (m._preSpiderfyLatlng) {
				m.setLatLng(m._preSpiderfyLatlng);
				delete m._preSpiderfyLatlng;
			}
			if (m.setZIndexOffset) {
				m.setZIndexOffset(0);
			}

			if (m._spiderLeg) {
				map.removeLayer(m._spiderLeg);
				delete m._spiderLeg;
			}
		}

		group._spiderfied = null;
	}
});

L.MarkerCluster.include(!L.DomUtil.TRANSITION ? {
	//Non Animated versions of everything
	_animationSpiderfy: function (childMarkers, positions) {
		var group = this._group,
			map = group._map,
			fg = group._featureGroup,
			i, m, leg, newPos;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			newPos = map.layerPointToLatLng(positions[i]);
			m = childMarkers[i];

			m._preSpiderfyLatlng = m._latlng;
			m.setLatLng(newPos);
			if (m.setZIndexOffset) {
				m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING
			}

			fg.addLayer(m);


			leg = new L.Polyline([this._latlng, newPos], { weight: 1.5, color: '#222' });
			map.addLayer(leg);
			m._spiderLeg = leg;
		}
		this.setOpacity(0.3);
		group.fire('spiderfied');
	},

	_animationUnspiderfy: function () {
		this._noanimationUnspiderfy();
	}
} : {
	//Animated versions here
	SVG_ANIMATION: (function () {
		return document.createElementNS('http://www.w3.org/2000/svg', 'animate').toString().indexOf('SVGAnimate') > -1;
	}()),

	_animationSpiderfy: function (childMarkers, positions) {
		var me = this,
			group = this._group,
			map = group._map,
			fg = group._featureGroup,
			thisLayerPos = map.latLngToLayerPoint(this._latlng),
			i, m, leg, newPos;

		//Add markers to map hidden at our center point
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			//If it is a marker, add it now and we'll animate it out
			if (m.setOpacity) {
				m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING
				m.setOpacity(0);
			
				fg.addLayer(m);

				m._setPos(thisLayerPos);
			} else {
				//Vectors just get immediately added
				fg.addLayer(m);
			}
		}

		group._forceLayout();
		group._animationStart();

		var initialLegOpacity = L.Path.SVG ? 0 : 0.3,
			xmlns = L.Path.SVG_NS;


		for (i = childMarkers.length - 1; i >= 0; i--) {
			newPos = map.layerPointToLatLng(positions[i]);
			m = childMarkers[i];

			//Move marker to new position
			m._preSpiderfyLatlng = m._latlng;
			m.setLatLng(newPos);
			
			if (m.setOpacity) {
				m.setOpacity(1);
			}


			//Add Legs.
			leg = new L.Polyline([me._latlng, newPos], { weight: 1.5, color: '#222', opacity: initialLegOpacity });
			map.addLayer(leg);
			m._spiderLeg = leg;

			//Following animations don't work for canvas
			if (!L.Path.SVG || !this.SVG_ANIMATION) {
				continue;
			}

			//How this works:
			//http://stackoverflow.com/questions/5924238/how-do-you-animate-an-svg-path-in-ios
			//http://dev.opera.com/articles/view/advanced-svg-animation-techniques/

			//Animate length
			var length = leg._path.getTotalLength();
			leg._path.setAttribute("stroke-dasharray", length + "," + length);

			var anim = document.createElementNS(xmlns, "animate");
			anim.setAttribute("attributeName", "stroke-dashoffset");
			anim.setAttribute("begin", "indefinite");
			anim.setAttribute("from", length);
			anim.setAttribute("to", 0);
			anim.setAttribute("dur", 0.25);
			leg._path.appendChild(anim);
			anim.beginElement();

			//Animate opacity
			anim = document.createElementNS(xmlns, "animate");
			anim.setAttribute("attributeName", "stroke-opacity");
			anim.setAttribute("attributeName", "stroke-opacity");
			anim.setAttribute("begin", "indefinite");
			anim.setAttribute("from", 0);
			anim.setAttribute("to", 0.5);
			anim.setAttribute("dur", 0.25);
			leg._path.appendChild(anim);
			anim.beginElement();
		}
		me.setOpacity(0.3);

		//Set the opacity of the spiderLegs back to their correct value
		// The animations above override this until they complete.
		// If the initial opacity of the spiderlegs isn't 0 then they appear before the animation starts.
		if (L.Path.SVG) {
			this._group._forceLayout();

			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i]._spiderLeg;

				m.options.opacity = 0.5;
				m._path.setAttribute('stroke-opacity', 0.5);
			}
		}

		setTimeout(function () {
			group._animationEnd();
			group.fire('spiderfied');
		}, 200);
	},

	_animationUnspiderfy: function (zoomDetails) {
		var group = this._group,
			map = group._map,
			fg = group._featureGroup,
			thisLayerPos = zoomDetails ? map._latLngToNewLayerPoint(this._latlng, zoomDetails.zoom, zoomDetails.center) : map.latLngToLayerPoint(this._latlng),
			childMarkers = this.getAllChildMarkers(),
			svg = L.Path.SVG && this.SVG_ANIMATION,
			m, i, a;

		group._animationStart();

		//Make us visible and bring the child markers back in
		this.setOpacity(1);
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			//Marker was added to us after we were spidified
			if (!m._preSpiderfyLatlng) {
				continue;
			}

			//Fix up the location to the real one
			m.setLatLng(m._preSpiderfyLatlng);
			delete m._preSpiderfyLatlng;
			//Hack override the location to be our center
			if (m.setOpacity) {
				m._setPos(thisLayerPos);
				m.setOpacity(0);
			} else {
				fg.removeLayer(m);
			}

			//Animate the spider legs back in
			if (svg) {
				a = m._spiderLeg._path.childNodes[0];
				a.setAttribute('to', a.getAttribute('from'));
				a.setAttribute('from', 0);
				a.beginElement();

				a = m._spiderLeg._path.childNodes[1];
				a.setAttribute('from', 0.5);
				a.setAttribute('to', 0);
				a.setAttribute('stroke-opacity', 0);
				a.beginElement();

				m._spiderLeg._path.setAttribute('stroke-opacity', 0);
			}
		}

		setTimeout(function () {
			//If we have only <= one child left then that marker will be shown on the map so don't remove it!
			var stillThereChildCount = 0;
			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];
				if (m._spiderLeg) {
					stillThereChildCount++;
				}
			}


			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];

				if (!m._spiderLeg) { //Has already been unspiderfied
					continue;
				}


				if (m.setOpacity) {
					m.setOpacity(1);
					m.setZIndexOffset(0);
				}

				if (stillThereChildCount > 1) {
					fg.removeLayer(m);
				}

				map.removeLayer(m._spiderLeg);
				delete m._spiderLeg;
			}
			group._animationEnd();
		}, 200);
	}
});


L.MarkerClusterGroup.include({
	//The MarkerCluster currently spiderfied (if any)
	_spiderfied: null,

	_spiderfierOnAdd: function () {
		this._map.on('click', this._unspiderfyWrapper, this);

		if (this._map.options.zoomAnimation) {
			this._map.on('zoomstart', this._unspiderfyZoomStart, this);
		}
		//Browsers without zoomAnimation or a big zoom don't fire zoomstart
		this._map.on('zoomend', this._noanimationUnspiderfy, this);

		if (L.Path.SVG && !L.Browser.touch) {
			this._map._initPathRoot();
			//Needs to happen in the pageload, not after, or animations don't work in webkit
			//  http://stackoverflow.com/questions/8455200/svg-animate-with-dynamically-added-elements
			//Disable on touch browsers as the animation messes up on a touch zoom and isn't very noticable
		}
	},

	_spiderfierOnRemove: function () {
		this._map.off('click', this._unspiderfyWrapper, this);
		this._map.off('zoomstart', this._unspiderfyZoomStart, this);
		this._map.off('zoomanim', this._unspiderfyZoomAnim, this);

		this._unspiderfy(); //Ensure that markers are back where they should be
	},


	//On zoom start we add a zoomanim handler so that we are guaranteed to be last (after markers are animated)
	//This means we can define the animation they do rather than Markers doing an animation to their actual location
	_unspiderfyZoomStart: function () {
		if (!this._map) { //May have been removed from the map by a zoomEnd handler
			return;
		}

		this._map.on('zoomanim', this._unspiderfyZoomAnim, this);
	},
	_unspiderfyZoomAnim: function (zoomDetails) {
		//Wait until the first zoomanim after the user has finished touch-zooming before running the animation
		if (L.DomUtil.hasClass(this._map._mapPane, 'leaflet-touching')) {
			return;
		}

		this._map.off('zoomanim', this._unspiderfyZoomAnim, this);
		this._unspiderfy(zoomDetails);
	},


	_unspiderfyWrapper: function () {
		/// <summary>_unspiderfy but passes no arguments</summary>
		this._unspiderfy();
	},

	_unspiderfy: function (zoomDetails) {
		if (this._spiderfied) {
			this._spiderfied.unspiderfy(zoomDetails);
		}
	},

	_noanimationUnspiderfy: function () {
		if (this._spiderfied) {
			this._spiderfied._noanimationUnspiderfy();
		}
	},

	//If the given layer is currently being spiderfied then we unspiderfy it so it isn't on the map anymore etc
	_unspiderfyLayer: function (layer) {
		if (layer._spiderLeg) {
			this._featureGroup.removeLayer(layer);

			layer.setOpacity(1);
			//Position will be fixed up immediately in _animationUnspiderfy
			layer.setZIndexOffset(0);

			this._map.removeLayer(layer._spiderLeg);
			delete layer._spiderLeg;
		}
	}
});


}(window, document));
define("leaflet-markercluster", ["leaflet"], (function (global) {
    return function () {
        var ret, fn;
       fn = function () {
        return L.MarkerClusterGroup;
      };
        ret = fn.apply(global, arguments);
        return ret || global.L.MarkerClusterGroup;
    };
}(this)));

define('aeris/maps/strategy/markers/clustericontemplate',[
  'aeris/util'
], function(_) {
  var templateString = '<div>' +
    '  <img src="{url}"' +
    '       ">' +
    '  <div style="position: absolute;' +
    '          bottom: {height - 8}px;' +
    '          right: -{width / 2}px;' +
    '          color: #fff;' +
    '          font-size: 12px;' +
    '          font-family: Arial,sans-serif;' +
    '          font-weight: bold;' +
    '          font-style: normal;' +
    '          text-decoration: none;' +
    '          text-align: center;' +
    '          width: 25px;' +
    '          line-height:1.5em;' +

                // Background image
    '           background-image: -webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(0%, #a90329), color-stop(44%, #8f0222), color-stop(100%, #6d0019));' +
    '           background-image: -webkit-linear-gradient(top, #a90329 0%, #8f0222 44%, #6d0019 100%);' +
    '           background-image: -moz-linear-gradient(top, #a90329 0%, #8f0222 44%, #6d0019 100%);' +
    '           background-image: -o-linear-gradient(top, #a90329 0%, #8f0222 44%, #6d0019 100%);' +
    '           background-image: linear-gradient(top, #a90329 0%, #8f0222 44%, #6d0019 100%);' +
    '           -webkit-border-radius: 5px;' +
    '           -moz-border-radius: 5px;' +
    '           -ms-border-radius: 5px;' +
    '           -o-border-radius: 5px;' +
    '           border-radius: 5px;' +
    '           border: 1px solid rgba(0, 0, 0, 0.5);' +
    '          ">' +
    '    {count}' +
    '  </div>' +
    '</div>';

  return _.template(templateString);
});

define('aeris/maps/strategy/markers/markercluster',[
  'aeris/util',
  'aeris/maps/abstractstrategy',
  'aeris/maps/strategy/util',
  'leaflet',
  'leaflet-markercluster',
  'aeris/maps/strategy/markers/clustericontemplate'
], function(_, AbstractStrategy, mapUtil, Leaflet, MarkerClusterGroup, clusterIconTemplate) {
  /**
   * A strategy for rendering clusters of markers
   * using Leaflet.
   *
   * @class aeris.maps.leaflet.MarkerCluster
   * @extends aeris.maps.AbstractStrategy
   *
   * @constructor
   */
  var MarkerCluster = function(markerCollection) {
    /**
     * A hash of MarkerClusterer objects,
     * referenced by group name.
     *
     * eg.
     *    {
     *      snow: {L.MarkerClusterGroup},
     *      rain: {L.MarkerClusterGroup}
     *    }
     *
     * @property view
     * @type {Object.<string,L.MarkerClusterGroup>}
     */

    AbstractStrategy.call(this, markerCollection);

    this.bindObjectToView_();
    this.addBulkMarkers_(this.object_.models);
  };
  _.inherits(MarkerCluster, AbstractStrategy);


  /**
   * @method createView_
   * @private
   */
  MarkerCluster.prototype.createView_ = function() {
    var view = {};

    // Create default cluster group
    view[MarkerCluster.SINGLE_CLUSTER_GROUPNAME_] = this.createCluster_(MarkerCluster.SINGLE_CLUSTER_GROUPNAME_);

    return view;
  };


  /**
   * @method setMap
   */
  MarkerCluster.prototype.setMap = function(map) {
    AbstractStrategy.prototype.setMap.call(this, map);

    // Add clusters to the map
    _.values(this.view_).
      forEach(function(view) {
        view.addTo(this.mapView_);
      }, this);

    // Add all markers to the clusters
    this.resetMarkers_(this.object_.models);
  };


  /**
   * @method beforeRemove_
   * @private
   */
  MarkerCluster.prototype.beforeRemove_ = function() {
    _.each(this.view_, function(clusterView) {
      this.mapView_.removeLayer(clusterView);
    }, this);
  };


  /**
   * @method createCluster_
   * @private
   * @param {string} type
   * @return {L.MarkerClusterGroup}
   */
  MarkerCluster.prototype.createCluster_ = function(type) {
    var cluster = new MarkerClusterGroup({
      iconCreateFunction: function(cluster) {
        return this.createIconForCluster_(cluster, type);
      }.bind(this)
    });

    this.proxyMouseEventsForCluster_(cluster);

    if (this.mapView_) {
      cluster.addTo(this.mapView_);
    }

    return cluster;
  };


  /**
   * @method createIconForCluster_
   * @private
   *
   * @param {L.MarkerCluster} cluster
   * @param {string} type Icon type category
   * @return {L.Icon}
   */
  MarkerCluster.prototype.createIconForCluster_ = function(cluster, type) {
    var clusterStyle = this.getClusterStyle_(cluster, type);

    return new L.DivIcon({
      html: this.createHtmlForCluster_(cluster, type),
      iconSize: new Leaflet.Point(clusterStyle.width, clusterStyle.height),
      iconAnchor: new Leaflet.Point(clusterStyle.offsetX, clusterStyle.offsetY),
      className: MarkerCluster.CLUSTER_CSS_CLASS_
    });
  };


  /**
   * @method createClusterHtml_
   * @private
   * @param {L.MarkerCluster} cluster
   * @param {string} type Icon type category
   * @return {string}
   */
  MarkerCluster.prototype.createHtmlForCluster_ = function(cluster, type) {
    var clusterStyle = this.getClusterStyle_(cluster, type);

    return clusterIconTemplate(_.extend({}, clusterStyle, {
      count: cluster.getChildCount()
    }));
  };


  /**
   * Chooses a cluster style based on the specified
   * cluster type, and the size of the cluster.
   *
   * @method getClusterStyle_
   * @private
   *
   * @param {L.MarkerCluster} cluster
   * @param {string} type
   * @return {Object=} Style options
   */
  MarkerCluster.prototype.getClusterStyle_ = function(cluster, type) {
    var clusterStyles = this.object_.getClusterStyle(type);
    var count = cluster.getChildCount();
    var index = -1;

    while (count !== 0) {
      count = parseInt(count / 10, 10);
      index++;
    }

    // Index resolves to:
    //      size < 10    ==>   0
    // 10 < size < 100   ==>   1
    // 100 < size < 1000 ==>   2
    // etc..
    index = Math.min(index, clusterStyles.length - 1);

    return clusterStyles[index];
  };


  /**
   * @method addBulkMarkers_
   * @param {Array.<aeris.maps.Marker>} markers
   * @private
   */
  MarkerCluster.prototype.addBulkMarkers_ = function(markers) {
    var markersByGroup = _.groupBy(markers, this.getMarkerType_, this);

    _.each(markersByGroup, function(markersInGroup, type) {
      var markerViews = markersInGroup.map(function(marker) {
        return marker.getView();
      });

      this.ensureClusterGroup_(type);

      markersInGroup.forEach(this.hideMarkerView_, this);

      this.view_[type].addLayers(markerViews);
    }, this);
  };


  /**
   * @method removeMarker_
   * @private
   * @param {aeris.maps.Marker} marker
   */
  MarkerCluster.prototype.removeMarker_ = function(marker) {
    this.getClusterForMarker_(marker).removeLayer(marker.getView());
  };


  /**
   * @method resetMarkers_
   * @private
   * @param {Array.<aeris.maps.Marker>=} opt_replacementMarkers
   */
  MarkerCluster.prototype.resetMarkers_ = function(opt_replacementMarkers) {
    _.invoke(this.view_, 'clearLayers');

    if (opt_replacementMarkers) {
      this.addBulkMarkers_(opt_replacementMarkers);
    }
  };


  /**
   * Hide a marker's view from the map,
   * without effecting the state of the marker object.
   *
   * @method hideMarkerView_
   * @private
   * @param {aeris.maps.Marker} marker
   */
  MarkerCluster.prototype.hideMarkerView_ = function(marker) {
    if (this.mapView_) {
      this.mapView_.removeLayer(marker.getView());
    }
  };


  /**
   * @method ensureClusterGroup_
   * @private
   * @param {string} type
   */
  MarkerCluster.prototype.ensureClusterGroup_ = function(type) {
    if (!this.view_[type]) {
      this.view_[type] = this.createCluster_(type);
    }
  };


  /**
   * @method getClusterForMarker_
   * @private
   * @param {aeris.maps.Marker} marker
   * @return {L.MarkerClusterGroup}
   */
  MarkerCluster.prototype.getClusterForMarker_ = function(marker) {
    var type = this.getMarkerType_(marker);
    return this.view_[type];
  };


  /**
   * @method getMarkerType_
   * @private
   * @param {aeris.maps.Marker} marker
   * @return {string} Marker group type
   */
  MarkerCluster.prototype.getMarkerType_ = function(marker) {
    return marker.getType() || MarkerCluster.SINGLE_CLUSTER_GROUPNAME_;
  };


  /**
   * @method bindObjectToView_
   * @private
   */
  MarkerCluster.prototype.bindObjectToView_ = function() {
    this.listenTo(this.object_, {
      'add': function() {
        this.addBulkMarkers_(this.object_.models);
      },
      'remove': this.removeMarker_,
      'reset': function() {
        this.resetMarkers_(this.object_.models);
      }
    });
  };


  /**
   * @method proxyMouseEvents_
   * @private
   * @param {L.MarkerClusterGroup} cluster
   */
  MarkerCluster.prototype.proxyMouseEventsForCluster_ = function(cluster) {
    cluster.on({
      'clusterclick': this.triggerMouseEvent_.bind(this, 'cluster:click'),
      'clustermouseover': this.triggerMouseEvent_.bind(this, 'cluster:mouseover'),
      'clustermouseout': this.triggerMouseEvent_.bind(this, 'cluster:mouseout')
    }, this);
  };


  /**
   * Trigger a click event on the {aeris.maps.markercollections.MarkerCollection}
   * object, by transforming a {L.MouseEvent} object.
   *
   * @method triggerMouseEvent_
   * @private
   * @param {string} eventName The event to fire on the MarkerCollection.
   * @param {L.MouseEvent} eventObj
   */
  MarkerCluster.prototype.triggerMouseEvent_ = function(eventName, eventObj) {
    var latLon = mapUtil.toAerisLatLon(eventObj.latlng);

    this.object_.trigger(eventName, latLon);
  };

  /**
   * @method destroy
   */
  MarkerCluster.prototype.destroy = function() {
    var markerViews = this.object_.models.
      map(function(marker) {
        return marker.getView();
      });
    var mapView = this.mapView_;

    AbstractStrategy.prototype.destroy.call(this);

    // Put each marker view back on the map.
    // --> we're destroying the clustering strategy,
    //    but we still want the markers to be rendered.
    if (mapView) {
      markerViews.forEach(function(view) {
        view.addTo(mapView);
      }, this);
    }
  };


  /**
   * @property SINGLE_CLUSTER_GROUP_NAME_
   * @constant
   * @type {string}
   * @private
   */
  MarkerCluster.SINGLE_CLUSTER_GROUPNAME_ = 'MARKERCLUSTERSTRATEGY_SINGLE_CLUSTER';


  /**
   * @property CLUSTER_CSS_CLASS_
   * @type {string}
   * @private
   */
  MarkerCluster.CLUSTER_CSS_CLASS_ = 'aeris-cluster aeris-leaflet';


  return MarkerCluster;
});
/**
 * @for aeris.maps.markercollections.MarkerCollection
 */
/**
 * @event cluster:click
 * @param {aeris.maps.LatLon} latLon
 */
/**
 * When the mouse enters a cluster.
 *
 * @event cluster:mouseover
 * @param {aeris.maps.LatLon} latLon
 */
/**
 * When the mouse exits a cluster.
 *
 * @event cluster:mouseout
 * @param {aeris.maps.LatLon} latLon
 */
;
define('aeris/maps/markercollections/markercollection',[
  'aeris/util',
  'aeris/maps/extensions/mapobjectcollection',
  'aeris/togglecollectionbehavior',
  'aeris/maps/extensions/strategyobject',
  'aeris/maps/markers/marker',
  'aeris/maps/markercollections/config/clusterstyles',
  'aeris/maps/strategy/markers/markercluster'
], function(_, MapObjectCollection, ToggleCollectionBehavior, StrategyObject, Marker, clusterStyles, MarkerClusterStrategy) {
  /**
   * A collection of {aeris.maps.markers.Marker} objects.
   *
   * By default, marker collections are rendered using a clustering strategy (eg MarkerClustererPlus for google maps).
   *
   * A MarkerCollection is a type of {aeris.ViewCollection}, which means that it can bind its attributes to a data collection ({aeris.Collection} or {Backbone.Collection}). Any changes, additions, or deletions to the bound data collection will be reflected in the marker collection.
   *
   * See {aeris.maps.markers.Marker} documentation for more information on transforming raw data into marker attributes. Note that `attributeTransforms` can be set directly on the MarkerCollection object using the `modelOptions` option:
   *
   * <code class="example">
   *    var markers = new aeris.maps.markercollections.MarkerCollection(null, {
   *      modelOptions: {
   *        attributeTransforms: {
   *          // ...
   *        }
   *      }
   *    });
   * </code>
   *
   * @class aeris.maps.markercollections.MarkerCollection
   * @extends aeris.maps.extensions.MapObjectCollection
   * @publicApi
   *
   * @uses aeris.maps.extensions.StrategyObject
   * @uses aeris.ToggleCollectionBehavior
   *
   * @constructor
   *
   * @param {Array.<aeris.maps.markers.Marker>=} opt_markers Markers to add to the collection.
   *
   * @param {Object=} opt_options
   * @param {Function=} opt_options.model Constructor for an {aeris.maps.markers.Marker} object.
   *                                  to use as a model for the collection.
   * @param {function():aeris.maps.AbstractStrategy=} opt_options.clusterStrategy Strategy for rendering marker clusters.
   * @param {aeris.maps.markercollections.options.ClusterStyles=} opt_options.clusterStyles
   * @param {Object=} opt_options.clusterOptions Options to pass onto the marker clusterer view.
   * @param {Boolean=} opt_options.cluster Whether to cluster markers. Default is true.
   * @param {string|aeris.maps.AbstractStrategy=} opt_options.strategy
  */
  var MarkerCollection = function(opt_markers, opt_options) {
    var options = _.defaults(opt_options || {}, {
      model: Marker,
      clusterStrategy: MarkerClusterStrategy,
      clusterStyles: {},
      clusterOptions: {},
      cluster: true,
      strategy: null
    });

    // Set default styles
    options.clusterStyles = _.defaults(options.clusterStyles, {
      defaultStyles: clusterStyles.defaultStyles
    });

    /**
     * @property clusterStyles_
     * @private
     * @type {aeris.maps.markercollections.options.ClusterStyles}
    */
    this.clusterStyles_ = options.clusterStyles;

    /**
     * @property clusterStrategy_
     * @private
     * @type {function():aeris.maps.AbstractStrategy}
    */
    this.clusterStrategy_ = options.clusterStrategy;

    /**
     * @property clusterOptions_
     * @private
     * @type {Object}
    */
    this.clusterOptions_ = options.clusterOptions;


    /**
     * Whether a clustering strategy
     * is currently active.
     *
     * @property isClustering_
     * @private
     * @type {Boolean}
    */
    this.isClustering_ = false;

    MapObjectCollection.call(this, opt_markers, options);
    ToggleCollectionBehavior.call(this);
    StrategyObject.call(this, {
      strategy: options.strategy
    });

    // Start up clustering
    if (options.cluster) {
      this.startClustering();
    }
  };
  _.inherits(MarkerCollection, MapObjectCollection);
  _.extend(MarkerCollection.prototype, StrategyObject.prototype);
  _.extend(MarkerCollection.prototype, ToggleCollectionBehavior.prototype);

  /**
   * Returns a copy of the
   * cluster styles for the specified
   * cluster group.
   *
   * @param {string=} opt_group Defaults to 'defaultStyles.'.
   * @return {Object} Cluster styles object.
   * @method getClusterStyle
   */
  MarkerCollection.prototype.getClusterStyle = function(opt_group) {
    var styles = this.clusterStyles_[opt_group] || this.clusterStyles_['defaultStyles'];

    return styles.slice(0);
  };


  /**
   * @method getClusterOptions
   * @return {Object}
   */
  MarkerCollection.prototype.getClusterOptions = function() {
    return _.clone(this.clusterOptions_);
  };


  /**
   * Starts up the strategy defined
   * by this.clusterStrategy_.
   * @method startClustering
   */
  MarkerCollection.prototype.startClustering = function() {
    this.setStrategy(this.clusterStrategy_);

    // Sometimes re-starting clustering
    // causes some non-clustered markers not to show.
    // This fixes that.
    var map = this.getMap();
    this.setMap(null);
    this.setMap(map);

    this.isClustering_ = true;
  };


  /**
   * Destroys the clustering strategy
   * started up by #startClustering.
   * @method stopClustering
   */
  MarkerCollection.prototype.stopClustering = function() {
    if (this.isClustering_) {
      this.removeStrategy();
    }
  };


  return _.expose(MarkerCollection, 'aeris.maps.markercollections.MarkerCollection');
});
/**
 * Style setting for marker clusters,
 * organized by marker type.
 *
 * Example:
 *  {
 *    snow: [
 *      {url: 'snow1.png', height: 10, width: 10},
 *      {url: 'snow2.png', height: 20, width: 20},
 *    ],
 *    rain: [
 *      {url: 'rain1.png', height: 10, width: 10},
 *      {url: 'rain2.png', height: 20, width: 20},
 *    ]
 *
 *    // Default styles for markers which do not implement
 *    // a getType method.
 *    defaultStyles: [ ... ]
 *
 *  }
 *
 * In order for markers to be clustered by type, each marker must
 * implement a `getType` method. Otherwise, markers will be styled using the
 * `defaultStyles` styles.
 *
 * See http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/docs/reference.html
 *  "class ClusterIconStyle" for acceptable style options.
 *
 * @class aeris.maps.markercollections.options.ClusterStyles
 * @static
*/
;
define('aeris/maps/markercollections/pointdatamarkers',[
  'aeris/util',
  'aeris/maps/markercollections/markercollection'
], function(_, MarkerCollection) {
  /**
   * A collection of {aeris.maps.markers.PointDataMarker} map objects.
   *
   * @class aeris.maps.markercollections.PointDataMarkers
   * @extends aeris.maps.markercollections.MarkerCollection
   *
   * @constructor
   * @override
  */
  var PointDataMarkers = function(opt_models, opt_options) {
    MarkerCollection.call(this, opt_models, opt_options);
  };
  _.inherits(PointDataMarkers, MarkerCollection);


  // Dynamically proxy {aeris.api.collections.AerisApiCollection}
  // params-interface methods
  _.each([
    'getParams',
    'setParams',
    'setFrom',
    'setTo',
    'setLimit',
    'setBounds',
    'addFilter',
    'removeFilter',
    'resetFilter',
    'addQuery',
    'removeQuery',
    'resetQuery',
    'getQuery'
  ], function(methodName) {
    PointDataMarkers.prototype[methodName] = function(var_args) {
      return this.data_[methodName].apply(this.data_, arguments);
    };
  });
  /**
   * Return params used to query the Aeris API.
   *
   * @method getParams
   * @return {aeris.api.params.models.Params}
   */
  /**
   * Set params used to query the Aeris API.
   *
   * @method setParams
   * @param {Object} params
   */
  /**
   * Set the `from` parameter for querying the Aeris API.
   *
   * @method setFrom
   * @param {Date} from
   */
  /**
   * Set the `to` parameter for querying the Aeris API.
   *
   * @method setTo
   * @param {Date} to
   */
  /**
   * Set the latLon bounds for querying the Aeris API.
   *
   * @method setBounds
   * @param {aeris.maps.Bounds} bounds
   */
  /**
   * Add a filter to the Aeris API request.
   *
   * @method addFilter
   * @param {string|Array.<string>} filter
   */
  /**
   * Remove a filter from the Aeris API request.
   *
   * @method removeFilter
   * @param {string|Array.<string>} filter
   */
  /**
   * Reset a filter from the Aeris API request.
   *
   * @method resetFilter
   * @param {string|Array.<string>=} opt_filter
   */
  /**
   * Add a query term to Aeris API request.
   *
   * @method addQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>} query
   */
  /**
   * Remove a query from the Aeris API request
   *
   * @method removeQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>|string|Array.<string>} Query model(s), or property (key).
   */
  /**
   * Resets the query for the Aeris API request.
   *
   * @method resetQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>=} opt_replacementQuery
   */
  /**
   * Returns the query for the Aeris API request.
   *
   * @method getQuery
   * @return {aeris.api.params.collections.ChainedQueries}
   */


  return PointDataMarkers;
});

define('aeris/api/operator',['aeris/util'], function(_) {
  /**
   * Logical operators for use in constructing
   * queries included in Aeris API requests.
   *
   * @class aeris.api.Operator
   * @static
   */
  var Operators = {
    AND: 'AND',
    OR: 'OR'
  };


  return _.expose(Operators, 'aeris.api.Operator');
});

define('aeris/api/params/models/filter',[
  'aeris/util',
  'aeris/model',
  'aeris/errors/validationerror',
  'aeris/api/operator'
], function(_, BaseModel, ValidationError, Operator) {
  /**
   * Represents a single filter applied
   * to a request to the Aeris API.
   *
   * @class aeris.api.params.models.Filter
   * @extends aeris.Model
   *
   * @param {Object=} opt_options
   * @constructor
   */
  var Filter = function(opt_attrs, opt_options) {
    /**
     * The name of the filter.
     *
     * @attribute name
     */
    /**
     * Operator to use when querying with multiple filters.
     *
     * Note that the filter operator will be used
     * before the filter name. Advanced order-of-operations
     * in filter operators is not currently supported.
     *
     * @attribute operator
     * @type {aeris.api.Operator}
     * @default {aeris.api.Operator.AND}
     */
    var attrs = _.extend({
      operator: Operator.AND
    }, opt_attrs);

    var options = _.defaults(opt_options || {}, {
      idAttribute: 'name'
    });

    BaseModel.call(this, attrs, options);

    // Validate when added to a collection
    this.listenTo(this, 'add', function() { this.isValid() });
  };
  _.inherits(Filter, BaseModel);


  /**
   * @method validate
   */
  Filter.prototype.validate = function(attrs) {
    // Validate operator
    if ([Operator.AND, Operator.OR].indexOf(attrs.operator) === -1) {
      return new ValidationError('Operator', 'Must be an aeris.api.Operator.');
    }
  };


  /**
   * @method isOr
   * @return {Boolean} Filter's operator is 'OR'.
   */
  Filter.prototype.isOr = function() {
    return this.get('operator') === Operator.OR;
  };


  /**
   * @method isAnd
   * @return {Boolean} Filter's operator is 'AND'.
   */
  Filter.prototype.isAnd = function() {
    return this.get('operator') === Operator.AND;
  };


  return _.expose(Filter, 'aeris.api.params.models.Filter');
});

define('aeris/api/params/collections/filtercollection',[
  'aeris/util',
  'aeris/collection',
  'aeris/api/params/models/filter',
  'aeris/api/operator'
], function(_, BaseCollection, Filter, Operator) {
  /**
   * Represents a set of filters to include in
   * a request to the AerisAPI
   *
   * @class aeris.api.params.collections.FilterCollection
   * @extends aeris.Collection
   *
   * @constructor
   *
   * @param {Array.<string>} opt_options.validFilters
   *                         A list of valid filters.
   *
   */
  var FilterCollection = function(opt_filters, opt_options) {
    var options = _.defaults(opt_options || {}, {
      model: Filter
    });


    BaseCollection.call(this, opt_filters, options);
  };
  _.inherits(FilterCollection, BaseCollection);


  /**
   * Prepares the filtercollection to be used
   * as for the `filter` parameters in a Aeris API
   * request query string.
   *
   * @override
   * @method toString
   */
  FilterCollection.prototype.toString = function() {
    var str = '';

    this.each(function(filter, n) {
      var operator = this.operatorToString_(filter.get('operator'));

      // First filter doesn't use operator
      if (n === 0) {
        str += filter.get('name');
        return;
      }

      str += operator + filter.get('name');
    }, this);

    return str;
  };


  /**
   * Encodes an {aeris.api.Operator} to be used in an AerisApi
   * query request. See http://www.hamweather.com/support/documentation/aeris/queries/.
   *
   * @method operatorToString_
   * @param {aeris.api.Operator} operator
   * @return {string}
   * @private
   */
  FilterCollection.prototype.operatorToString_ = function(operator) {
    var operatorMap = {};
    operatorMap[Operator.AND] = ',';
    operatorMap[Operator.OR] = ';';

    return operatorMap[operator];
  };


  /**
   * Provides an alternate syntax for aeris.Collection#add,
   * which allows adding filters by name.
   *
   * eg.
   *  filters.add('sieve', 'colander', { operator: aeris.api.Operator.OR });
   *
   * @param {string|Array.<string>|aeris.api.params.filter.Filter|Array.<aeris.api.params.filter.Filter>} filters
   *        Filter name(s), or Filter model(s).
   * @param {Object=} opt_options
   * @param {aeris.api.Operator} opt_options.operator
   *
   * @override
   * @method add
   */
  FilterCollection.prototype.add = function(filters, opt_options) {
    var options = opt_options || {};
    options.reset = false;

    this.addFiltersByName_(filters, options);
  };

  /**
   * Provides an alternate syntax for aeris.Collection#reset,
   * which allows adding filters by name.
   *
   * eg.
   *  filters.reset('sieve', 'colander', { operator: aeris.api.Operator.AND });
   *
   * @param {string|Array.<string>|aeris.api.params.filter.Filter|Array.<aeris.api.params.filter.Filter>} filters
   *        Filter name(s), or Filter model(s).
   * @param {Object=} opt_options
   * @param {string} opt_options.operator This operator will be applied to all filters.
   *
   * @override
   * @method reset
   */
  FilterCollection.prototype.reset = function(filters, opt_options) {
    var options = opt_options || {};
    options.reset = true;

    this.addFiltersByName_(filters, options);
  };


  /**
   * Adds a filter or array of filters by name,
   * or delegates to aeris.Collection#add or aeris.Collection#reset
   *
   * @param {string|Array.<string>|aeris.api.params.filter.Filter|Array.<aeris.api.params.filter.Filter>} filters
   *
   * @param {Object} options
   * @param {string=} options.operator This operator will be applied to all filters.
   * @param {Boolean} options.reset If true, will use aeris.Collection#reset to add the models.
   * @private
   * @method addFiltersByName_
   */
  FilterCollection.prototype.addFiltersByName_ = function(filters, options) {
    var addMethod, modelsToAdd = [];

    // Set default options
    options = _.extend({
      operator: Operator.AND,
      reset: false
    }, options);

    // Use either 'add' or 'reset' method
    addMethod = options.reset ? 'reset' : 'add';

    // Normalize filters param as array
    _.isArray(filters) || _.isUndefined(filters) || (filters = [filters]);

    // Standard parameters --> delegate to parent Collection#add
    if (!filters || !_.isString(filters[0])) {
      return BaseCollection.prototype[addMethod].call(this, filters);
    }


    // Create filter models
    _.each(filters, function(filterName) {
      modelsToAdd.push({
        name: filterName,
        operator: options.operator
      });
    }, this);

    // Call the parent method (add or reset) with the generated models.
    return BaseCollection.prototype[addMethod].call(this, modelsToAdd, options);
  };


  /**
   * Allows to remove filters by name, in addition
   * to standard aeris.Collection#remove syntax.
   *
   * @param {string|Array.<string>|aeris.api.params.filter.Filter|Array.<aeris.api.params.filter.Filter>} filters
   *        Filter name(s), or Filter model(s).
   * @method remove
   */
  FilterCollection.prototype.remove = function(filters, opt_options) {
    var modelsToRemove = [];

    // Normalize filters as array
    _.isArray(filters) || (filters = [filters]);

    // Standard parameters --> delegate to parent Collection#remove
    if (!_.isString(filters[0])) {
      return BaseCollection.prototype.remove.apply(this, arguments);
    }

    // Find models with matching filter names
    _.each(filters, function(filterName) {
      var matches = this.where({
        name: filterName
      });
      modelsToRemove = modelsToRemove.concat(matches);
    }, this);

    return BaseCollection.prototype.remove.call(this, modelsToRemove, opt_options);
  };


  return FilterCollection;
});

define('aeris/api/params/models/query',[
  'aeris/util',
  'aeris/model',
  'aeris/errors/validationerror',
  'aeris/api/operator'
], function(_, Model, ValidationError, Operator) {
  /**
   * Represents a single query property:value
   * definition.
   *
   * @class aeris.api.params.models.Query
   * @extends aeris.Model
   *
   * @constructor
   */
  var Query = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      idAttribute: 'property',
      defaults: {}
    });

    _.defaults(options.defaults, {
      operator: Operator.AND
    });

    /**
     * @attribute property
     * @type {string}
     */
    /**
     * @attribute value
     * @type {*}
     */
    /**
     * The operator to use when
     * appending this query pair onto
     * a chained query.
     *
     * See 'Chaining Queries and Filters':
     *  http://www.hamweather.com/support/documentation/aeris/queries/
     *
     * @attribute operator
     * @type {aeris.api.Operator}
     */

    Model.call(this, opt_attrs, options);

    // Validate on ctor.
    this.isValid();
  };
  _.inherits(Query, Model);


  /**
   * @method validate
   */
  Query.prototype.validate = function(attrs) {
    var validOperators = [Operator.AND, Operator.OR];

    if (!_.isString(attrs.property)) {
      return new ValidationError('property', attrs.property + ' is not a valid query property');
    }
    if (!attrs.value) {
      return new ValidationError('value', 'Value is not defined.');
    }
    if (_.indexOf(validOperators, attrs.operator) === -1) {
      return new ValidationError('operator', attrs.operator + ' is not a valid query operator. ' +
        'Valid operators include: \'' + validOperators.join('\', \'') + '\'.');
    }
  };


  /**
   * Custom toString,
   * for converting to query string.
   *
   * @override
   * @return {string}
   * @method toString
   */
  Query.prototype.toString = function() {
    this.isValid();

    return this.get('property') + ':' + this.get('value');
  };


  return Query;
});

define('aeris/api/params/collections/chainedqueries',[
  'aeris/util',
  'aeris/collection',
  'aeris/api/params/models/query',
  'aeris/api/operator'
], function(_, Collection, Query, Operator) {
  /**
   * A collection of {aeris.api.params.models.Query} objects,
   * which may be chained together with an operator.
   *
   * See 'Chained Queries and Filters':
   *  http://www.hamweather.com/support/documentation/aeris/queries/
   *
   * @class aeris.api.params.collections.ChainedQueries
   * @extends aeris.Collection
   *
   * @constructor
   * @override
  */
  var ChainedQueries = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      model: Query
    });

    Collection.call(this, opt_models, options);
  };
  _.inherits(ChainedQueries, Collection);


  /**
   * Custom toString method,
   * or query-stringification.
   *
   * @override
   * @method toString
   */
  ChainedQueries.prototype.toString = function() {
    var operatorLookup = {};
    operatorLookup[Operator.AND] = ',';
    operatorLookup[Operator.OR] = ';';

    return this.reduce(function(memo, query, i) {
      var operator = operatorLookup[query.get('operator')];

      // Add operator,
      // (just not for the first prop:value pair.
      if (i !== 0) {
        memo = memo + operator;
      }

      return memo + query.toString();
    }, '', this);
  };


  return ChainedQueries;
});

define('aeris/errors/unimplementedmethoderror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.UnimplementedMethodError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'UnimplementedMethodError'
  });
});

define('aeris/helpers/validator/abstractvalidator',[
  'aeris/util',
  'aeris/errors/unimplementedmethoderror'
], function(_, UnimplementedMethodError) {
  /**
   * Base validator class.
   *
   * @class aeris.helpers.validator.AbstractValidator
   * @implements aeris.helpers.validator.ValidatorInterface
   *
   * @abstract
   * @constructor
   */
  var AbstractValidator = function() {
    this.lastError_ = null;
  };

  /**
   * @method isValid
   */
  AbstractValidator.prototype.isValid = function() {
    throw new UnimplementedMethodError('ValidatorInterface#isValid');
  };

  /**
   * @method getLastError
   */
  AbstractValidator.prototype.getLastError = function() {
    return this.lastError_;
  };

  /**
   * @private
   * @method clearLastError_
   */
  AbstractValidator.prototype.clearLastError_ = function() {
    this.lastError_ = null;
  };

  /**
   * @private
   * @method setLastError_
   */
  AbstractValidator.prototype.setLastError_ = function(error) {
    this.lastError_ = error;
  };


  return AbstractValidator;
});

define('aeris/helpers/validator/boundsvalidator',[
  'aeris/util',
  'aeris/helpers/validator/abstractvalidator',
  'aeris/errors/validationerror'
], function(_, AbstractValidator, ValidationError) {
  /**
   * Validates bounds defined by latLon coordinates.
   *
   * @class aeris.helpers.validator.BoundsValidator
   * @extends aeris.helpers.validator.AbstractValidator
   *
   * @constructor
   * @override
  */
  var BoundsValidator = function(bounds) {
    this.bounds_ = bounds;

    AbstractValidator.apply(this, arguments);
  };
  _.inherits(BoundsValidator, AbstractValidator);


  /**
   * @method isValid
   */
  BoundsValidator.prototype.isValid = function() {
    var validationError = null;

    this.clearLastError_();

    function proposeError(error) {
      validationError || (validationError = error);
    }

    proposeError(this.validateArea_());

    if (!this.hasCoordinates_()) {
      proposeError(this.createValidationError_('Invalid bounds coordinates'));
    }

    this.setLastError_(validationError);
    return !validationError;
  };


  BoundsValidator.prototype.createValidationError_ = function(msg) {
    return new ValidationError('bounds', msg);
  };


  BoundsValidator.prototype.hasCoordinates_ = function() {
    return _.isArray(this.getSW_()) && _.isArray(this.getNE_());
  };


  BoundsValidator.prototype.validateArea_ = function() {
    var area;
    var validationError = null;

    try {
      area = this.getAreaOfBounds_();
    }
    catch (err) {
      var areaErrorMessage = 'Unable to calculate bounds area: ' + err.message;
      validationError = validationError || this.createValidationError_(areaErrorMessage);
    }

    if (!area || area <= 0) {
      validationError = validationError || this.createValidationError_('Bounds must define an area.');
    }

    return validationError;
  };


  BoundsValidator.prototype.getSW_ = function() {
    return this.bounds_[0];
  };


  BoundsValidator.prototype.getNE_ = function() {
    return this.bounds_[1];
  };


  BoundsValidator.prototype.getAreaOfBounds_ = function() {
    var sw, ne, height, width;

    sw = this.bounds_[0];
    ne = this.bounds_[1];

    height = Math.abs(ne[0] - sw[0]);
    width = Math.abs(ne[1] - sw[1]);

    return width * height;
  };


  return BoundsValidator;
});

define('aeris/api/params/models/params',[
  'aeris/util',
  'aeris/config',
  'aeris/model',
  'aeris/api/params/collections/filtercollection',
  'aeris/api/params/collections/chainedqueries',
  'aeris/errors/validationerror',
  'aeris/helpers/validator/boundsvalidator'
], function(_, aerisConfig, Model, Filters, ChainedQueries, ValidationError, BoundsValidator) {
  /**
   * Represents parameters to be included
   * with a request to the Aeris API.
   *
   * @class aeris.api.params.models.Params
   * @extends aeris.Model
   *
   * @param {Object=} opt_options
   * @param {function():aeris.api.params.collections.FilterCollection=} opt_options.FilterCollectionType Constructor for filter.
   * @param {function():aeris.api.params.collections.ChainedQueries=} opt_options.QueryType Constructor for query attr model.
   *
   * @constructor
   */
  var Params = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      FilterCollectionType: Filters,
      QueryType: ChainedQueries,
      validate: true
    });

    var attrs = _.defaults(opt_attrs || {}, {
      /**
       * Location parameter
       *
       * @attribute p
       * @type {string|aeris.maps.LatLon}
       */
      p: null,

      /**
       * @attribute filter
       * @type {aeris.api.params.collections.FilterCollection}
       */
      filter: [],

      /**
       * @attribute query
       * @type {aeris.api.params.collections.ChainedQueries}
       */
      query: [],

      /**
       * @attribute client_id
       * @type {string}
       */
      client_id: aerisConfig.get('apiId'),

      /**
       * @attribute client_secret
       * @type {string}
       */
      client_secret: aerisConfig.get('apiSecret')
    });


    /**
     * @type {function():aeris.api.params.collections.ChainedQuery} Constructor for query attribute object.
     * @private
     * @property QueryType_
     */
    this.QueryType_ = options.QueryType;


    /**
     * @property FilterCollectionType_
     * @private
     * @type {function():aeris.api.params.collections.FilterCollection}
     */
    this.FilterCollectionType_ = options.FilterCollectionType;


    // Process query/filter attrs provided as raw objects
    if (!(attrs.query instanceof this.QueryType_)) {
      attrs.query = new this.QueryType_(attrs.query);
    }
    if (!(attrs.filter instanceof this.FilterCollectionType_)) {
      attrs.filter = new this.FilterCollectionType_(attrs.filter);
    }

    Model.call(this, attrs, options);


    this.proxyEventsForAttr_('query');
    this.proxyEventsForAttr_('filter');

    this.bindToApiKeys_();
  };
  _.inherits(Params, Model);


  /**
   * @method validate
   */
  Params.prototype.validate = function(attrs) {
    var placeError = this.validatePlace_(attrs.p);

    if (placeError) {
      return placeError;
    }

    if (attrs.query && !(attrs.query instanceof this.QueryType_)) {
      return new ValidationError('query', attrs.query + ' is not a valid Query');
    }
  };


  /**
   * Bind client_id/secret params to
   * global apiKey config.
   *
   * @private
   * @method bindToApiKeys_
   */
  Params.prototype.bindToApiKeys_ = function() {
    this.listenTo(aerisConfig, 'change:apiId change:apiSecret', function() {
      this.set({
        client_id: this.get('client_id') || aerisConfig.get('apiId'),
        client_secret: this.get('client_secret') || aerisConfig.get('apiSecret')
      });
    });
  };


  Params.prototype.validatePlace_ = function(p) {
    var NO_ERROR = void 0;
    var isPlaceName = _.isString(p);
    var isZipCode = _.isNumeric(p);
    var isLatLon = _.isArray(p) && _.isNumeric(p[0]);
    var boundsError;

    if (_.isNull(p)) {
      return NO_ERROR;
    }
    if (isPlaceName) {
      return NO_ERROR;
    }
    if (isZipCode) {
      return NO_ERROR;
    }
    if (isLatLon) {
      return NO_ERROR;
    }

    boundsError = this.validateBounds_(p);

    if (boundsError) {
      return boundsError;
    }
  };


  Params.prototype.validateBounds_ = function(bounds) {
    var boundsValidator = new BoundsValidator(bounds);

    if (!boundsValidator.isValid()) {
      return boundsValidator.getLastError();
    }
  };


  /**
   * @method toJSON
   */
  Params.prototype.toJSON = function() {
    var json = Model.prototype.toJSON.apply(this, arguments);


    _.each(json, function(param, paramName) {
      // Clean out null, undefined, and empty arrays
      var isEmptyArray = _.isArray(param) && !param.length;
      if (_.isNull(param) || _.isUndefined(param) || isEmptyArray) {
        delete json[paramName];
        return;
      }

      // Convert dates to UNIX timestamps
      if (param instanceof Date) {
        json[paramName] = Math.ceil(param.getTime() / 1000);
      }
    });

    // Convert filter to comma-separated string
    if (this.get('filter')) {
      json.filter = this.get('filter').toString();
    }

    if (this.get('query')) {
      json.query = this.get('query').toString();
    }

    // Convert place polygon to comma-separated string
    if (_.isArray(this.get('p'))) {
      json.p = this.get('p').join(',');
    }

    return json;
  };


  /**
   * Sets the bound limits within which
   * to search for.
   *
   * If null is passed, will remove the bounds limit
   * parameter altogether.
   *
   * @param {?Array.<Array.<number>>} bounds Array of SW and NE lat/lons.
   * @method setBounds
   */
  Params.prototype.setBounds = function(bounds) {
    if (_.isNull(bounds)) {
      this.unset('p');
      return;
    }

    this.set({
      p: bounds
    }, { validate: true });
  };


  /**
   * Add a filter
   * Delegates to aeris.api.params.collections.FilterCollection#add
   * @method addFilter
   */
  Params.prototype.addFilter = function(filters, opt_options) {
    this.get('filter').add(filters, opt_options);
  };


  /**
   * Remove a filter.
   *
   * Delegates to aeris.api.params.collections.FilterCollection#remove
   * @method removeFilter
   */
  Params.prototype.removeFilter = function(filters, opt_options) {
    this.get('filter').remove(filters, opt_options);
  };

  /**
   * Resets the filters.
   *
   * Delegates to aeris.api.params.collections.FilterCollection#reset
   * @method resetFilter
   */
  Params.prototype.resetFilter = function(filters, opt_options) {
    this.get('filter').reset(filters, opt_options);
  };


  /**
   * Add a query term to Aeris API request.
   *
   * @method addQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>} query
   * @param {Object=} opt_options
   */
  Params.prototype.addQuery = function(query, opt_options) {
    this.get('query').add(query, opt_options);
  };

  /**
   * Remove a query from the Aeris API request
   *
   * @method removeQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>|string|Array.<string>} query model(s), or property (key).
   * @param {Object=} opt_options
   */
  Params.prototype.removeQuery = function(query, opt_options) {
    this.get('query').remove(query, opt_options);
  };

  /**
   * Resets the query for the Aeris API request.
   *
   * @method resetQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>=} opt_query
   * @param {Object=} opt_options
   */
  Params.prototype.resetQuery = function(opt_query, opt_options) {
    this.get('query').reset(opt_query, opt_options);
  };


  /**
   * Returns the query for the Aeris API request.
   *
   * @method getQuery
   * @return {aeris.api.params.collections.ChainedQueries}
   */
  Params.prototype.getQuery = function() {
    return this.get('query');
  };


  /**
   * Proxy events for a nested {aeris.Model|aeris.Collection} object.
   *
   * @method proxyEventsForAttr_
   * @private
   * @param {string} attr Attribute name of the nested object.
   */
  Params.prototype.proxyEventsForAttr_ = function(attr) {
    // Bind to current query model
    if (this.get(attr) && this.get(attr).on) {
      this.listenTo(this.get(attr), 'add remove change reset', function(model, opts) {
        this.trigger('change:' + attr, this, this.get(attr), opts);
        this.trigger('change', this, opts);
      });
    }
  };


  return Params;
});

define('aeris/errors/apiresponseerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.APIResponseError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'APIResponseError'
  });
});

define('aeris/api/mixins/aerisapibehavior',[
  'aeris/util',
  'aeris/model',
  'aeris/promise',
  'aeris/api/params/models/params',
  'aeris/errors/invalidargumenterror',
  'aeris/errors/apiresponseerror'
], function(_, Model, Promise,  Params, InvalidArgumentError, ApiResponseError) {
  /**
   * @class aeris.api.mixins.AerisApiBehavior
   */
  return {
    /**
     * A request has been made to fetch
     * data from the Aeris API.
     *
     * @event 'request'
     * @param {aeris.api.mixins.AerisApiBehavior} object Data object making the request.
     * @param {aeris.Promise} promise Promise to fetch data. Resolves with raw data.
     * @param {Object} requestOptions
     */

    /**
     * The AerisAPI has responsed to a request,
     * and the data object has updated with fetched data.
     *
     * @event 'sync'
     * @param {aeris.api.mixins.AerisApiBehavior} object Data object which made the request.
     * @param {Object} resp Raw response data from the AerisAPI.
     * @param {Object} requestOptions
     */

    /**
     * @protected
     * @param {Object|Model} opt_params
     * @return {aeris.api.params.models.Params}
     * @method createParams_
     */
    createParams_: function(opt_params) {
      return (opt_params instanceof Model) ?
        opt_params : new Params(opt_params, { validate: true });
    },

    /**
     * Returns the params object
     * used to fetch collection data.
     *
     * @return {aeris.api.params.models.Params}
     * @method getParams
     */
    getParams: function() {
      return this.params_;
    },

    /**
     * Updates the requests params
     * included with API requests.
     *
     * @param {string|Object} key Param name. First argument can also.
     *                    be a key: value hash.
     * @param {*} value Param value.
     * @method setParams
     */
    setParams: function(key, value) {
      // Delegate to AerisApiParams#set
      var args = Array.prototype.slice.call(arguments, 0);
      args.push({ validate: true });

      this.params_.set.apply(this.params_, args);
    },

    /**
     * @method setFrom
     * @param {Date} from
     */
    setFrom: function(from) {
      this.setParams('from', from);
    },

    /**
     * @method setTo
     * @param {Date} to
     */
    setTo: function(to) {
      this.setParams('to', to);
    },

    /**
     * @method setLimit
     * @param {number} limit
     */
    setLimit: function(limit) {
      this.setParams('limit', limit);
    },

    /**
     * @method setBounds
     * @param {aeris.maps.Bounds} bounds
     */
    setBounds: function(bounds) {
      this.params_.setBounds(bounds);
    },


    /**
     * Add a filter to the Aeris API request.
     *
     * @method addFilter
     * @param {string|Array.<string>|aeris.api.params.models.Filter|aeris.api.params.collections.FilterCollection} filter
     * @param {Object=} opt_options
     * @param {aeris.api.Operator} opt_options.operator
     */
    addFilter: function(filter, opt_options) {
      this.params_.addFilter(filter, opt_options);
    },

    /**
     * Remove a filter from the Aeris API request.
     *
     * @method removeFilter
     * @param {string|Array.<string>|aeris.api.params.models.Filter|aeris.api.params.collections.FilterCollection} filter
     * @param {Object=} opt_options
     */
    removeFilter: function(filter, opt_options) {
      this.params_.removeFilter(filter, opt_options);
    },

    /**
     * Reset a filter from the Aeris API request.
     *
     * @method resetFilter
     * @param {string|Array.<string>|aeris.api.params.models.Filter|aeris.api.params.collections.FilterCollection} opt_filter
     * @param {Object=} opt_options
     * @param {aeris.api.Operator} opt_options.operator
     */
    resetFilter: function(opt_filter, opt_options) {
      this.params_.resetFilter(opt_filter, opt_options);
    },

    /**
     * Add a query term to Aeris API request.
     *
     * @method addQuery
     * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>} query
     * @param {Object=} opt_options
     */
    addQuery: function(query, opt_options) {
      this.params_.addQuery(query, opt_options);
    },

    /**
     * Remove a query from the Aeris API request
     *
     * @method removeQuery
     * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>|string|Array.<string>} query model(s), or property (key).
     * @param {Object=} opt_options
     */
    removeQuery: function(query, opt_options) {
      this.params_.removeQuery(query, opt_options);
    },

    /**
     * Resets the query for the Aeris API request.
     *
     * @method resetQuery
     * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>=} opt_query
     * @param {Object=} opt_options
     */
    resetQuery: function(opt_query, opt_options) {
      this.params_.resetQuery(opt_query, opt_options);
    },


    /**
     * Returns the query for the Aeris API request.
     *
     * @method getQuery
     * @return {aeris.api.params.collections.ChainedQueries}
     */
    getQuery: function() {
      return this.params_.getQuery();
    },

    /**
     * Overrides Backbone.sync
     * to introduce logic for fetching
     * data from the Aeris API
     *
     * Note that the AerisAPI is read-only.
     *
     * @throws {aeris.errors.InvalidArgumentError} If a non-read request is made.
     * @return {aeris.Promise} Resolves with response data.
     *
     * @override
     * @protected
     * @method sync
     */
    sync: function(method, model, opt_options) {
      var data;
      var noop = function() {};
      var promiseToSync = new Promise();
      var options = _.defaults(opt_options || {}, {
        success: noop,
        error: noop,
        complete: noop
      });

      // Restrict requests to be read-only
      if (method !== 'read') {
        throw new InvalidArgumentError('Unable to send a ' + method + ' request ' +
          'to the Aeris API. The Aeris API is read-only');
      }

      // Trigger start of request,
      // as specified in Backbone docs,
      // and implemented by original sync method.
      this.trigger('request', this, promiseToSync, options);

      data = this.serializeParams_(this.params_);


      this.jsonp_.get(this.getEndpointUrl_(), data, _.bind(function(res) {
        if (!this.isSuccessResponse_(res)) {
          promiseToSync.reject(this.createErrorFromResponse_(res));
        }
        else {
          promiseToSync.resolve(res);
          this.trigger('sync', this, res, options);
        }
      }, this));


      return promiseToSync.
        done(options.success).
        fail(options.error).
        always(options.complete);
    },


    /**
     * Does the response object signal
     * a succesful API response?
     *
     * @param {Object} res Raw response data
     * @protected
     * @return {Boolean}
     */
    isSuccessResponse_: function(res) {
      return !res.error;
    },


    /**
     * @method createErrorFromResponse_
     * @protected
     * @param {Object} response
     * @return {Error}
     */
    createErrorFromResponse_: function(response) {
      var error;
      try {
        error = new ApiResponseError(response.error.description);
        error.code = response.error.code;
        error.responseObject = response;
      }
      catch (e) {
        error = new ApiResponseError(e.message);
      }

      return error;
    },


    /**
     * Convert the model's Params object
     * into a JSON data object.
     *
     * @method serializeParams_
     * @protected
     * @param {aeris.api.params.models.Params} params
     * @return {Object}
     */
    serializeParams_: function(params) {
      return params.toJSON();
    },


    /**
     * Fetch data from the Aeris API.
     *
     * @method fetch
     * @override
     * @return {aeris.Promise} Resolves with API response.
     */

    /**
     * @protected
     * @return {string}
     * @method getEndpointUrl_
     */
    getEndpointUrl_: function() {
      return _.compact([
        this.server_,
        this.endpoint_,
        this.action_
      ]).join('/') + '/';
    },

    /**
     * @method getEndpoint
     * @return {string}
     */
    getEndpoint: function() {
      return this.endpoint_;
    },


    /**
     * @method getAction
     * @return {string}
     */
    getAction: function() {
      return this.action_;
    },

    /**
     * @method setAction
     * @param {string} action
     */
    setAction: function(action) {
      this.action_ = action;
    },

    /**
     * @method parse
     * @protected
     */
    parse: function(res) {
      return res.response ? res.response : res;
    }
  };
});

define('aeris/jsonp',['aeris/util'], function(_) {

  _.provide('aeris.jsonp');

  /*
   * Lightweight JSONP fetcher
   * Copyright 2010-2012 Erik Karlsson. All rights reserved.
   * BSD licensed
   */


  /**
   * Usage:
   *
   * JSONP.get( 'someUrl.php', {param1:'123', param2:'456'}, function(data){
   *   //do something with data, which is the JSON object you should retrieve from someUrl.php
   * });
   *
   * @class aeris.JSONP
   */
  aeris.jsonp = (function() {

    var head, window = this, config = {};

    function load(url, pfnError) {
      var script = document.createElement('script'),
        done = false;
      script.src = url;
      script.async = true;

      var errorHandler = pfnError || config.error;
      if (typeof errorHandler === 'function') {
        script.onerror = function(ex) {
          errorHandler({url: url, event: ex});
        };
      }

      script.onload = script.onreadystatechange = function() {
        if (!done && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete')) {
          done = true;
          script.onload = script.onreadystatechange = null;
          if (script && script.parentNode) {
            script.parentNode.removeChild(script);
          }
        }
      };

      if (!head) {
        head = document.getElementsByTagName('head')[0];
      }
      head.appendChild(script);
    }

    function encode(str) {
      return encodeURIComponent(str);
    }

    function jsonp(url, params, callback, uniqueName, callbackName) {
      var query = (url || '').indexOf('?') === -1 ? '?' : '&', key;

      callbackName = (callbackName || config['callbackName'] || 'callback');
      uniqueName = uniqueName || callbackName + _.uniqueId('_aeris_jsonp_');

      params = params || {};
      for (key in params) {
        if (params.hasOwnProperty(key)) {
          query += encode(key) + '=' + encode(params[key]) + '&';
        }
      }

      window[uniqueName] = function(data) {
        callback(data);
        try {
          delete window[uniqueName];
        } catch (e) {}
        window[uniqueName] = null;
      };

      load(url + query + callbackName + '=' + uniqueName);
      return uniqueName;
    }

    function setDefaults(obj) {
      config = obj;
    }

    return {
      get: jsonp,
      init: setDefaults
    };

  }());

  return aeris.jsonp;

});

define('aeris/api/collections/aerisapicollection',[
  'aeris/util',
  'aeris/collection',
  'aeris/api/mixins/aerisapibehavior',
  'aeris/jsonp'
], function(_, Collection, AerisApiBehavior, JSONP) {
  /**
   * A data collection which creates {aeris.Model} objects
   * from Aeris API response data.
   *
   * See http://www.hamweather.com/support/documentation/aeris/
   * for Aeris API documentation.
   *
   * @class aeris.api.collections.AerisApiCollection
   * @extends aeris.Collection
   * @uses aeris.api.mixins.AerisApiBehavior
   *
   *
   * @constructor
   * @param {Object=} opt_models
   *
   * @param {Object=} opt_options
   * @param {string=} opt_options.endpoint Aeris API endpoint.
   * @param {Object|Model=} opt_options.params Parameters with which to query the Aeris API.
   * @param {string=} opt_options.server The Aeris API server location.
   * @param {aeris.JSONP=} opt_options.JSONP object used for fetching batch data.
   */
  var AerisApiCollection = function(opt_models, opt_options) {
    var options = _.extend({
      endpoint: '',
      action: '',
      params: {},
      server: '//api.aerisapi.com',
      jsonp: JSONP
    }, opt_options);


    /**
     * Aeris API Endpoints from which
     * to request data.
     *
     * See http://www.hamweather.com/support/documentation/aeris/endpoints/
     * for available endpoints, actions, and parameters.
     *
     * @type {string}
     * @private
     * @property endpoint_
     */
    this.endpoint_ = options.endpoint;


    /**
     * Aeris API Action
     *
     * See http://www.hamweather.com/support/documentation/aeris/actions/
     *
     * @type {string}
     * @private
     * @property action_
     */
    this.action_ = options.action;


    /**
     * Location of the Aeris API server.
     *
     * @type {string}
     * @private
     * @property server_
     */
    this.server_ = options.server;


    /**
     * Parameters to include with the batch request.
     *
     * Note that parameters can also be attached
     * to individual endpoints defined in this.endpoints_.
     *
     * @type {aeris.api.params.models.Params|Object}
     *       Will be converted to Params instance, if passed in as a plain object.
     * @protected
     * @property params_
     */
    this.params_ = this.createParams_(options.params);


    /**
     * @type {aeris.JSONP}
     * @private
     * @property jsonp_
     */
    this.jsonp_ = options.jsonp;


    Collection.call(this, opt_models, options);
  };
  _.inherits(AerisApiCollection, Collection);
  _.extend(AerisApiCollection.prototype, AerisApiBehavior);


  /**
   * @method parse
   */
  AerisApiCollection.prototype.parse = function(data) {
    // This is a hack for dealing with nested id attributes.
    // Model data is not otherwise parsed (on fetch) before checking for
    // duplicates in a collection. This results in duplicate models in a collection.
    // See https://github.com/jashkenas/backbone/issues/3147#issuecomment-43108388
    // and http://jsfiddle.net/tT2D9/3/
    var rawModels = AerisApiBehavior.parse.call(this, data);
    var parsedModels = rawModels.map(this.model.prototype.parse);

    return parsedModels;
  };


  return _.expose(AerisApiCollection, 'aeris.api.AerisApiCollection');
});

define('aeris/api/models/aerisapimodel',[
  'aeris/util',
  'aeris/api/mixins/aerisapibehavior',
  'aeris/model',
  'aeris/jsonp'
], function(_, AerisApiBehavior, Model, JSONP) {
  /**
   * A client-side representation of a single response object
   * from the Aeris API.
   *
   * @class aeris.api.models.AerisApiModel
   * @extends aeris.Model
   * @uses aeris.api.mixins.AerisApiBehavior
   *
   * @constructor
   * @override
   *
   * @param {Object=} opt_attrs
   * @param {Object=} opt_options
   * @param {string} opt_options.endpoint
   * @param {string} opt_options.action
   * @param {aeris.api.params.Params} opt_options.params
   */
  var AerisApiModel = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: '',
      action: '',
      params: {},
      jsonp: JSONP,
      server: '//api.aerisapi.com'
    });


    /**
     * Aeris API Endpoints from which
     * to request data.
     *
     * See http://www.hamweather.com/support/documentation/aeris/endpoints/
     * for available endpoints, actions, and parameters.
     *
     * @type {string}
     * @private
     * @property endpoint_
     */
    this.endpoint_ = options.endpoint;


    /**
     * Aeris API Action
     *
     * See http://www.hamweather.com/support/documentation/aeris/actions/
     *
     * @type {string}
     * @private
     * @property action_
     */
    this.action_ = options.action;


    /**
     * The locatin of the aeris API server.
     *
     * @type {string}
     * @private
     * @default 'http://api.aerisapi.com'
     * @property server_
     */
    this.server_ = options.server;


    /**
     * The JSONP utility for fetching AerisApi data.
     *
     * @type {aeris.JSONP}
     * @private
     * @property jsonp_
     */
    this.jsonp_ = options.jsonp || JSONP;


    /**
     * Parameters to include with the batch request.
     *
     * Note that parameters can also be attached
     * to individual endpoints defined in this.endpoints_.
     *
     * @type {aeris.api.params.models.Params|Object}
     *       Will be converted to Params instance, if passed in as a plain object.
     * @protected
     * @property params_
     */
    this.params_ = this.createParams_(options.params);


    Model.call(this, opt_attrs, options);
  };
  _.inherits(AerisApiModel, Model);
  _.extend(AerisApiModel.prototype, AerisApiBehavior);


  /**
   * @return {*|string}
   * @protected
   */
  AerisApiModel.prototype.getEndpointUrl_ = function() {
    var url = AerisApiBehavior.getEndpointUrl_.call(this);

    if (this.id) {
      url += this.id;
    }

    return url;
  };


  /**
   * Tests whether a model is passing
   * an Aeris API filter.
   *
   * @method testFilter
   * @param {string} filter
   * @return {Boolean}
   */
  AerisApiModel.prototype.testFilter = function(filter) {
    return true;
  };


  /**
   * Tests whether a model is passing a set
   * of Aeris API filters.
   *
   * @method testFilterCollection
   * @param {aeris.api.params.collections.FilterCollection} filters
   * @return {Boolean}
   */
  AerisApiModel.prototype.testFilterCollection = function(filters) {
    return filters.reduce(function(isPassingPreviousFilters, filterModel) {
      var isFirstFilter = _.isNull(isPassingPreviousFilters);

      var isPassingThisFilter = this.testFilter(filterModel.id);
      var isPassingBoth = isPassingThisFilter && isPassingPreviousFilters;
      var isPassingEither = isPassingThisFilter || isPassingPreviousFilters;

      if (isFirstFilter) {
        return isPassingThisFilter;
      }

      // If operator is 'AND', model must pass the current filter,
      // as the previous filters.
      return filterModel.isAnd() ? isPassingBoth : isPassingEither;
    }, null, this);
  };


  /**
   * @method parse
   */
  AerisApiModel.prototype.parse = function(res) {
    var data;

    if (_.isArray(res)) {
      data = res[0];
    }
    else if (res.response) {
      if (_.isArray(res.response)) {
        data = res.response[0];
      }
      else {
        data = res.response;
      }
    }
    else {
      data = res;
    }

    return data || {};
  };


  return _.expose(AerisApiModel, 'aeris.api.models.AerisApiModel');
});

define('aeris/api/models/pointdata',[
  'aeris/util',
  'aeris/errors/validationerror',
  'aeris/errors/apiresponseerror',
  'aeris/api/models/aerisapimodel'
], function(_, ValidationError, ApiResponseError, AerisApiModel) {
  /**
   * A base class for data
   * which is tied to a specified
   * lat/lon location.
   *
   * @class aeris.api.models.PointData
   * @extends aeris.api.models.AerisApiModel
   * @constructor
   */
  var PointData = function(opt_attrs, opt_options) {
    /**
     * @attribute latLon
     * @type {aeris.maps.LatLon}
     */

    var options = _.extend({
      validate: true
    }, opt_options);

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(PointData, AerisApiModel);



  return PointData;
});

define('aeris/api/collections/pointdatacollection',[
  'aeris/util',
  'aeris/api/collections/aerisapicollection',
  'aeris/api/models/pointdata'
], function(_, AerisApiCollection, PointData) {
  /**
   * A representation of point data from the Aeris Api.
   *
   * @class aeris.api.collections.PointDataCollection
   * @extends aeris.api.collections.AerisApiCollection
   *
   * @constructor
   * @override
   */
  var PointDataCollection = function(opt_models, opt_options) {
    var options = _.extend({
      validate: true,
      model: PointData,
      params: {}
    }, opt_options);

    _.defaults(options.params, {
      limit: 100,
      p: ':auto',
      radius: '3000mi'
    });

    AerisApiCollection.call(this, opt_models, options);
  };
  _.inherits(PointDataCollection, AerisApiCollection);


  return PointDataCollection;
});

define('aeris/subsetcollection',[
  'aeris/util',
  'aeris/collection',
  'aeris/promise',
  'aeris/errors/invalidargumenterror'
], function(_, Collection, Promise, InvalidArgumentError) {
  /**
   * A collection which acts as a subset of another (source) collection.
   *
   * A SubsetCollection defines rules for filtering models
   * from a source collection. The SubsetCollection will sync to all
   * changes in the source collection. If filtered rules are changed on the
   * SubsetCollection, it will be updated with models from the soure collection
   * accordingly
   *
   * @class aeris.SubsetCollection
   * @extends aeris.Collection
   *
   * @constructor
   *
   * @param {aeris.Collection|Array.<aeris.Model>} sourceCollection
   *
   * @param {Object=} opt_options
   * @param {?number=} opt_options.limit
   * @param {?function():Boolean=} opt_options.filter
   *
   * @throws {aeris.errors.InvalidArgumentError} If an invalid sourceCollection is provided.
   */
  var SubsetCollection = function(sourceCollection, opt_options) {
    var options = _.defaults(opt_options || {}, {
      limit: null,
      filter: null
    });

    /**
     * @type {aeris.Collection}
     * @protected
     */
    this.sourceCollection_ = this.normalizeSourceCollection_(sourceCollection);


    /**
     * @property limit_
     * @protected
     * @type {?number} If null, no limit is enforced.
     */
    this.limit_ = options.limit;


    /**
     * @property filter_
     * @private
     * @type {?function():Boolean}
     */
    this.filter_ = options.filter;


    Collection.call(this, this.getFilteredSourceModels_(), options);

    this.bindToSourceCollection_();
    this.proxyRequestEvents_();
  };
  _.inherits(SubsetCollection, Collection);


  /**
   * @method normalizeSourceCollection_
   * @private
   */
  SubsetCollection.prototype.normalizeSourceCollection_ = function(sourceCollection) {
    if (sourceCollection instanceof Collection) {
      return sourceCollection;
    }
    if (_.isArray(sourceCollection)) {
      return new Collection(sourceCollection);
    }

    throw new InvalidArgumentError(sourceCollection + ' is not a valid source collection');
  };


  /**
   * @method proxyRequestEvents_
   * @private
   */
  SubsetCollection.prototype.proxyRequestEvents_ = function() {
    this.listenTo(this.sourceCollection_, {
      request: function(collection, promiseToSync) {
        this.trigger('request', collection, promiseToSync);
      },
      sync: function(collection, resp) {
        this.trigger('sync', collection, resp);
      },
      error: function(collection, resp) {
        this.trigger('error', collection, resp);
      }
    });
  };


  /**
   * @method bindToSourceCollection_
   * @private
   */
  SubsetCollection.prototype.bindToSourceCollection_ = function() {
    this.listenTo(this.sourceCollection_, {
      'reset change sort': this.resetToSourceModel_,

      remove: function(model, sourceCollection, options) {
        var subsetModels;

        if (this.contains(model)) {
          // Remove the corresponding subsetCollection model
          this.remove(model);

          // If we freed up some room by removing that model,
          // and the next available model from the source collection.
          if (this.limit_ && this.isUnderLimit()) {
            subsetModels = this.getFilteredSourceModels_();
            this.add(_.last(subsetModels));
          }
        }
      },

      add: function(model, sourceCollection, options) {
        var wasModelAppended, filteredSourceModels;
        var doesAddedModelPassFilter = _.isNull(this.filter_) || this.filter_(model);

        // Model doesn't pass our filter, so
        // we're just going to pretend this never happened...
        if (!doesAddedModelPassFilter) {
          return;
        }

        // Model was appended to the end of the source collection
        // --> we can append it onto our collection.
        wasModelAppended = _.isUndefined(options.at);
        if (wasModelAppended && this.isUnderLimit()) {
          this.add(model);
        }

        // Model was added in the middle of the sourceCollection
        // so we need to make sure to add it in the right spot
        else {
          filteredSourceModels = this.getFilteredSourceModels_();

          if (_.contains(filteredSourceModels, model)) {
            // Add the model at the correct index
            this.add(model, {
              at: filteredSourceModels.indexOf(model)
            });

            // Remove the model which was pushed over the limit
            if (this.isOverLimit()) {
              this.pop();
            }
          }
        }
      }
    });
  };


  /**
   * @method resetToSourceModel_
   * @private
   */
  SubsetCollection.prototype.resetToSourceModel_ = function() {
    this.set(this.getFilteredSourceModels_());
  };


  /**
   * @method getFilteredSourceModels_
   * @protected
   * @return {Array.<aeris.Model>} Models from the source collection
   *                              which pass subset filtering rules.
   */
  SubsetCollection.prototype.getFilteredSourceModels_ = function() {
    var filteredSourceModels = _.isNull(this.filter_) ?
      this.sourceCollection_.models :
      this.sourceCollection_.filter(this.filter_);

    var limit = _.isNull(this.limit_) ? filteredSourceModels.length : this.limit_;
    var limitedModels = filteredSourceModels.slice(0, limit);
    return limitedModels;
  };


  /**
   * @method getSourceCollection
   * @return {aeris.Collection}
   */
  SubsetCollection.prototype.getSourceCollection = function() {
    return this.sourceCollection_;
  };


  /**
   * Sets a filter to be used when syncing the
   * SubsetCollection to it's source collection.
   *
   * The filter receives a source collection models
   * as an argument.
   * If the filter returns true, the model will be added
   * to the SubsetCollection.
   *
   * Set the filter to null to disable filtering.
   *
   * @method setFilter
   * @param {function(aeris.Model):Boolean} filter
   * @param {Object=} opt_ctx Context to set on filter function.
   */
  SubsetCollection.prototype.setFilter = function(filter, opt_ctx) {
    this.filter_ = filter;

    if (opt_ctx && !_.isNull(filter)) {
      this.filter_ = _.bind(this.filter_, opt_ctx);
    }

    this.resetToSourceModel_();
  };


  /**
   * Stops filtering models from the source collection.
   *
   * @method removeFilter
   */
  SubsetCollection.prototype.removeFilter = function() {
    this.setFilter(null);

    this.resetToSourceModel_();
  };


  /**
   * Limits the number of models from the source collection
   * to set on the SubsetCollection.
   *
   * @method setLimit
   * @param {number} limit
   */
  SubsetCollection.prototype.setLimit = function(limit) {
    this.limit_ = limit;

    this.resetToSourceModel_();
  };


  /**
   * Stops limiting the number of models from
   * the source collection to set on the SubsetCollection.
   *
   * @method removeLimit
   */
  SubsetCollection.prototype.removeLimit = function() {
    this.limit_ = null;

    this.resetToSourceModel_();
  };


  /**
   * Does the collection have fewer models
   * than the specified limit?
   *
   * If no limit is set, this will always
   * return true.
   *
   * @method isUnderLimit
   * @return {Boolean}
   */
  SubsetCollection.prototype.isUnderLimit = function() {
    return _.isNull(this.limit_) || this.length < this.limit_;
  };

  /**
   * @method isOverLimit
   * @return {Boolean}
   */
  SubsetCollection.prototype.isOverLimit = function() {
    var isAtLimit = this.length === this.sourceCollection_.length;

    return !this.isUnderLimit() && !isAtLimit;
  };


  /**
   * Fetches data from the underlying source collection.
   *
   * @method fetch
   * @param {Object=} opt_options
   * @return {aeris.Promise}
   */
  SubsetCollection.prototype.fetch = function(opt_options) {
    return this.sourceCollection_.fetch(opt_options);
  };


  return SubsetCollection;
});

define('aeris/api/collections/aerisapiclientcollection',[
  'aeris/util',
  'aeris/subsetcollection',
  'aeris/api/collections/aerisapicollection'
], function(_, SubsetCollection, AerisApiCollection) {
  /**
   * A subset of an {aeris.api.collections.AerisApiCollection},
   * which may be manipulated on the client side, without effecting
   * models retrieved from the server.
   *
   * For example, a ClientCollection will filter models based
   * on provided filter params, without removing models from the
   * AerisApiCollection. Since we saved previously stored models,
   * the filter can be changed or removed without needed to request
   * new data from the server.
   *
   * @class aeris.api.collections.AerisApiClientCollection
   * @extends aeris.SubsetCollection
   *
   * @constructor
   * @override
   *
   * @param {Array.<aeris.Model>=} opt_models
   *
   * @param {Object=} opt_options
   * @param {string=} opt_options.endpoint Aeris API endpoint.
   * @param {Object|Model=} opt_options.params Parameters with which to query the Aeris API.
   * @param {string=} opt_options.server The Aeris API server location.
   *
   * @param {number=} clientLimit Max number of models to retain in the client collection.
   * @param {function(aeris.api.models.AerisApiModel):Boolean} clientFilter Filter to apply to the client collection.
  */
  var AerisApiClientCollection = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      SourceCollectionType: AerisApiCollection,
      clientLimit: null,
      clientFilter: _.constant(true)
    });

    var aerisApiOptions = _.pick(options, [
      'params',
      'endpoint',
      'action',
      'model',
      'server',
      'SourceCollectionType'
    ]);

    var sourceCollection = this.createSourceCollection_(opt_models, aerisApiOptions);

    var subsetCollectionOptions = {
      limit: options.clientLimit,
      filter: options.clientFilter
    };

    SubsetCollection.call(this, sourceCollection, subsetCollectionOptions);


    this.bindClientFilters_();
    this.updateClientFilters_();

    /**
     * A request has been made to fetch
     * data from the Aeris API.
     *
     * @event 'request'
     * @param {aeris.api.mixins.AerisApiBehavior} object Data object making the request.
     * @param {aeris.Promise} promise Promise to fetch data. Resolves with raw data.
     * @param {Object} requestOptions
     */

    /**
     * The AerisAPI has responsed to a request,
     * and the data object has updated with fetched data.
     *
     * @event 'sync'
     * @param {aeris.api.mixins.AerisApiBehavior} object Data object which made the request.
     * @param {Object} resp Raw response data from the AerisAPI.
     * @param {Object} requestOptions
     */
  };
  _.inherits(AerisApiClientCollection, SubsetCollection);


  /**
   * @method createSourceCollection_
   * @private
   * @param {Array.<aeris.Model>} opt_models
   * @param {Object=} options Options to pass to the source collection.
   * @param {function():aeris.api.collections.AerisApiCollection} options.SourceCollectionType source collection constructor.
   */
  AerisApiClientCollection.prototype.createSourceCollection_ = function(opt_models, options) {
    return new options.SourceCollectionType(opt_models, _.pick(options || {}, [
      'params',
      'endpoint',
      'action',
      'model',
      'server'
    ]));
  };


  /**
   * @method bindClientFilters_
   * @private
   */
  AerisApiClientCollection.prototype.bindClientFilters_ = function() {
    this.listenTo(this.getApiFilters_(), {
      'add remove reset change': this.updateClientFilters_
    });
  };


  /**
   * @method updateClientFilters_
   * @private
   */
  AerisApiClientCollection.prototype.updateClientFilters_ = function() {
    if (this.getApiFilters_().length) {
      this.applyClientFilters_(this.getApiFilters_());
    }
    else {
      this.removeClientFilter();
    }
  };


  /**
   * @method applyClientFilters_
   * @private
   * @param {aeris.api.params.collections.FilterCollection} filterCollection
   */
  AerisApiClientCollection.prototype.applyClientFilters_ = function(filterCollection) {
    this.setClientFilter(function(apiModel) {
      return apiModel.testFilterCollection(filterCollection);
    }, this);
  };


  /**
   * Get the filters used to query the AerisAPI
   *
   * @method getApiFilters_
   * @private
   * @return {aeris.api.params.collections.FilterCollection}
   */
  AerisApiClientCollection.prototype.getApiFilters_ = function() {
    return this.sourceCollection_.getParams().get('filter');
  };


  /**
   * Apply a client-side filter to the collection.
   *
   * @method setClientFilter
   * @param {function():Boolean} filter
   * @param {Object=} opt_ctx Filter context.
   */
  AerisApiClientCollection.prototype.setClientFilter = function(filter, opt_ctx) {
    SubsetCollection.prototype.setFilter.call(this, filter, opt_ctx);
  };


  /**
   * Remove all client-side filters from the collection.
   *
   * @method removeClientFilter
   */
  AerisApiClientCollection.prototype.removeClientFilter = function() {
    SubsetCollection.prototype.removeFilter.call(this);
  };


  /**
   * Sets a limit on how many
   *
   * @param {number} limit
   */
  AerisApiClientCollection.prototype.setClientLimit = function(limit) {
    SubsetCollection.prototype.setLimit.call(this, limit);
  };


  /**
   * @method removeClientLimit
   */
  AerisApiClientCollection.prototype.removeClientLimit = function() {
    SubsetCollection.prototype.removeLimit.call(this);
  };



  /**
   * Returns the params object
   * used to fetch collection data.
   *
   * @return {aeris.api.params.models.Params}
   * @method getParams
   */
  /**
   * Updates the requests params
   * included with API requests.
   *
   * @param {string|Object} key Param name. First argument can also.
   *                    be a key: value hash.
   * @param {*} value Param value.
   * @method setParams
   */
  /**
   * @method setFrom
   * @param {Date} from
   */
  /**
   * @method setTo
   * @param {Date} to
   */
  /**
   * @method setLimit
   * @param {number} limit
   */
  /**
   * @method setBounds
   * @param {aeris.maps.Bounds} bounds
   */
  /**
   * Add a filter to the Aeris API request.
   * Filters will also be applied client-side, if possible.
   *
   * @method addFilter
   * @param {string|Array.<string>|aeris.api.params.models.Filter|aeris.api.params.collections.FilterCollection} filter
   * @param {Object=} opt_options
   * @param {aeris.api.Operator} opt_options.operator
   */
  /**
   * Remove a filter from the Aeris API request.
   * Filters will also be applied client-side, if possible.
   *
   * @method removeFilter
   * @param {string|Array.<string>|aeris.api.params.models.Filter|aeris.api.params.collections.FilterCollection} filter
   * @param {Object=} opt_options
   */
  /**
   * Reset a filter from the Aeris API request.
   * Filters will also be applied client-side, if possible.
   *
   * @method resetFilter
   * @param {string|Array.<string>|aeris.api.params.models.Filter|aeris.api.params.collections.FilterCollection} opt_filter
   * @param {Object=} opt_options
   * @param {aeris.api.Operator} opt_options.operator
   */
  /**
   * Add a query term to Aeris API request.
   *
   * @method addQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>} query
   * @param {Object=} opt_options
   */
  /**
   * Remove a query from the Aeris API request
   *
   * @method removeQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>|string|Array.<string>} query model(s), or property (key).
   * @param {Object=} opt_options
   */
  /**
   * Resets the query for the Aeris API request.
   *
   * @method resetQuery
   * @param {aeris.api.params.models.Query|Array.<aeris.api.params.models.Query>=} opt_query
   * @param {Object=} opt_options
   */
  /**
   * Returns the query for the Aeris API request.
   *
   * @method getQuery
   * @return {aeris.api.params.collections.ChainedQueries}
   */
  /**
   * Fetch data from the Aeris API.
   *
   * @method fetch
   * @override
   * @return {aeris.Promise} Resolves with API response.
   */

  // Proxy AerisApi methods
  var aerisApiProxyMethods = [
    'getParams',
    'setParams',
    'setFrom',
    'setTo',
    'setBounds',
    'addFilter',
    'removeFilter',
    'resetFilter',
    'addQuery',
    'removeQuery',
    'resetQuery',
    'getQuery',
    'setAction',
    'getAction'
  ];
  _.each(aerisApiProxyMethods, function(methodName) {
    AerisApiClientCollection.prototype[methodName] = function() {
      return this.sourceCollection_[methodName].apply(this.sourceCollection_, arguments);
    };
  }, this);


  return AerisApiClientCollection;
});

define('aeris/api/models/earthquake',[
  'aeris/util',
  'aeris/api/models/pointdata',
  'aeris/errors/apiresponseerror'
], function(_, PointData, ApiResponseError) {
  /**
   * @publicApi
   * @class aeris.api.models.Earthquake
   * @extends aeris.api.models.PointData
   *
   * @constructor
   * @override
   */
  var Earthquake = function(opt_attrs, opt_options) {
    PointData.call(this, opt_attrs, opt_options);
  };
  _.inherits(Earthquake, PointData);


  /**
   * @method parse
   */
  Earthquake.prototype.parse = function(res) {
    var attrs = PointData.prototype.parse.apply(this, arguments);

    if (!res.report || !res.report.id) {
      throw new ApiResponseError('Missing earthquake id');
    }

    attrs.id = res.report.id;

    return attrs;
  };


  /**
   * @method testFilter
   */
  Earthquake.prototype.testFilter = function(filter) {
    if (filter === 'shallow') {
      return this.isShallow();
    }

    return filter === this.getAtPath('report.type');
  };


  /**
   * Is the earthquake less than 70km deep.
   *
   * @method isShallow
   * @private
   * @return {Boolean}
   */
  Earthquake.prototype.isShallow = function() {
    return this.getAtPath('report.depthKM') < 70;
  };


  return _.expose(Earthquake, 'aeris.api.models.Earthquake');
});

define('aeris/datehelper',['aeris/util'], function(_) {
  var MILLISECOND = 1;
  var SECOND = MILLISECOND * 1000;
  var MINUTE = SECOND * 60;
  var HOUR = MINUTE * 60;
  var DAY = HOUR * 24;
  var WEEK = DAY * 7;


  /**
   * Manipulates a {Date} object.
   *
   * @publicApi
   * @class aeris.DateHelper
   *
   * @param {Date=} opt_date Defaults to current date.
   * @constructor
   */
  var DateHelper = function(opt_date) {
    this.date_ = opt_date || new Date();
  };


  /**
   * @param {number} ms
   * @return {Date} Modified date object.
   * @method addMilliseconds
   */
  DateHelper.prototype.addMilliseconds = function(ms) {
    this.date_.setTime(this.date_.getTime() + ms);

    return this;
  };


  /**
   * @param {number} seconds
   * @return {Date} Modified date object.
   * @method addSeconds
   */
  DateHelper.prototype.addSeconds = function(seconds) {
    return this.addMilliseconds(seconds * SECOND);
  };


  /**
   * @param {number} minutes
   * @return {Date} Modified date object.
   * @method addMinutes
   */
  DateHelper.prototype.addMinutes = function(minutes) {
    return this.addMilliseconds(minutes * MINUTE);
  };


  /**
   * @param {number} hours
   * @return {Date} Modified date object.
   * @method addHours
   */
  DateHelper.prototype.addHours = function(hours) {
    return this.addMilliseconds(hours * HOUR);
  };


  /**
   * @param {number} days
   * @return {Date} Modified date object.
   * @method addDays
   */
  DateHelper.prototype.addDays = function(days) {
    return this.addMilliseconds(days * DAY);
  };


  /**
   * @param {number} weeks
   * @return {Date} Modified date object.
   * @method addWeeks
   */
  DateHelper.prototype.addWeeks = function(weeks) {
    return this.addMilliseconds(weeks * WEEK);
  };

  /**
   * @method add
   * @param {number} hours
   * @param {number=} opt_minutes
   * @param {number=} opt_seconds
   * @param {number=} opt_milliseconds
   */
  DateHelper.prototype.addTime = function(hours, opt_minutes, opt_seconds, opt_milliseconds) {
    var minutes = _.isUndefined(opt_minutes) ? 0 : opt_minutes;
    var seconds = _.isUndefined(opt_seconds) ? 0 : opt_seconds;
    var milliseconds = _.isUndefined(opt_milliseconds) ? 0 : opt_milliseconds;

    return this.addHours(hours).
      addMinutes(minutes).
      addSeconds(seconds).
      addMilliseconds(milliseconds);
  };


  /**
   * @return {Date}
   * @method getDate
   */
  DateHelper.prototype.getDate = function() {
    return this.date_;
  };


  /**
   * @method getTime
   */
  DateHelper.prototype.getTime = function() {
    return this.date_.getTime();
  };


  /**
   * @param {Date} opt_date Defaults to current date.
   * @method setDate
   */
  DateHelper.prototype.setDate = function(opt_date) {
    this.date_ = opt_date || new Date();

    return this;
  };


  return _.expose(DateHelper, 'aeris.DateHelper');
});

define('aeris/api/collections/earthquakes',[
  'aeris/util',
  'aeris/api/collections/pointdatacollection',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/earthquake',
  'aeris/datehelper'
], function(_, PointDataCollection, AerisApiClientCollection, Earthquake, DateHelper) {
  /**
   * A representation of earthquake data from the
   * Aeris API 'earthquake' endpoint.
   *
   * @publicApi
   * @class aeris.api.collections.Earthquakes
   * @extends aeris.api.collections.PointDataCollection
   *
   * @constructor
   * @override
   */
  var Earthquakes = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      params: {},
      model: Earthquake,
      endpoint: 'earthquakes',
      action: 'within',
      SourceCollectionType: PointDataCollection
    });

    _.defaults(options.params, {
      from: new DateHelper().addWeeks(-7).getDate(),
      to: new Date(),
      radius: '3000miles'
    });

    AerisApiClientCollection.call(this, opt_models, options);

    /**
     * @property sourceCollection_
     * @type {aeris.api.collections.PointDataCollection}
     */
  };
  _.inherits(Earthquakes, AerisApiClientCollection);


  return _.expose(Earthquakes, 'aeris.api.collections.Earthquakes');
});

define('aeris/maps/markers/pointdatamarker',[
  'aeris/util',
  'aeris/maps/markers/marker',
  'aeris/config'
], function(_, Marker, config) {
  /**
   * A marker MapExtensionObject which is a view
   * model for a {aeris.api.models.PointData} data model.
   *
   * @class aeris.maps.markers.PointDataMarker
   * @extends aeris.maps.markers.Marker
   *
   * @constructor
   * @override
   */
  var PointDataMarker = function(opt_attrs, opt_options) {
    var attrs = _.defaults(opt_attrs || {}, {
      offsetX: 12,
      offsetY: 12
    });
    var options = _.defaults(opt_options || {}, {
      iconPath: '{name}',
      iconLookup: {},
      typeAttribute: ''
    });

    options.attributeTransforms = _.defaults(options.attributeTransforms || {}, {
      url: this.lookupUrl_,
      selectedUrl: this.lookupSelectedUrl_,
      title: this.lookupTitle_,
      position: this.lookupPosition_,
      type: this.lookupType_,
      offsetX: this.lookupOffsetX_,
      offsetY: this.lookupOffsetY_
    });


    /**
     * The type category this marker belongs
     * to. Useful organizing markers which
     * match some filter.
     *
     * @attribute type
     * @type {string}
     */


    /**
     * The path to a icon url, where {name}
     * is the name of the icon defined in this.iconLookup_
     *
     * @type {string}
     * @private
     * @property iconPath_
     */
    this.iconPath_ = options.iconPath;

    /**
     * The path to the icon url,
     * to use only when the marker is selected.
     *
     * Defaults to the iconPath.
     *
     * @property selectedIconPath_
     * @private
     * @type {string}
     */
    this.selectedIconPath_ = options.selectedIconPath || options.iconPath;


    /**
     * An object to lookup a marker's icon
     * url by it's type.
     *
     * eg: { blizzard: 'storm/icon_blizzard_sm', snow: 'stormicon_snow_sm' }
     *
     * @type {*|{}}
     * @private
     * @property iconLookup_
     */
    this.iconLookup_ = options.iconLookup;


    /**
     * The data attribute used to categorize the marker.
     *
     * Defined as a '.' delimited string.
     * eg. 'weather.type' would map to this.get('data').get('weather').type;
     *
     * @type {*|string}
     * @private
     * @property typeAttribute_
     */
    this.typeAttribute_ = options.typeAttribute;


    Marker.call(this, attrs, options);
  };
  _.inherits(PointDataMarker, Marker);


  /**
   * The type category of this marker.
   * Generally, corresponds to a data filter.
   *
   * @override
   * @method getType
   * @return {string}
   */
  PointDataMarker.prototype.getType = function() {
    return this.get('type');
  };


  /**
   * Lookup the marker type.
   *
   * @return {string}
   * @protected
   * @method lookupType_
   */
  PointDataMarker.prototype.lookupType_ = function() {
    var type = this.getDataAttribute(this.typeAttribute_);
    var match;

    // For multiple types
    // find one with a matching partner
    // in the iconLookup object.
    if (_.isArray(type)) {
      _.each(type, function(singleType) {
        if (_.has(this.iconLookup_, singleType)) {
          match = singleType;
        }
      }, this);

      return match || this.get('type');
    }

    return type || this.get('type');
  };


  /**
   * Lookup the icon url.
   *
   * @return {string}
   * @protected
   * @method lookupUrl_
   */
  PointDataMarker.prototype.lookupUrl_ = function() {
    var iconConfig = this.getIconConfig_();

    // If no icon name found,
    // don't try to set one.
    if (!iconConfig) {
      return this.get('url');
    }

    return this.iconPath_.replace(/\{name\}/, iconConfig.url);
  };


  /**
   * @method lookupSelectedUrl_
   * @private
   */
  PointDataMarker.prototype.lookupSelectedUrl_ = function() {
    var selectedUrl;
    var iconConfig = this.getIconConfig_();

    // If no icon name found,
    // don't try to set one.
    if (!iconConfig) {
      return this.get('selectedUrl');
    }

    selectedUrl = iconConfig.selectedUrl || iconConfig.url;

    return this.selectedIconPath_.replace(/\{name\}/, selectedUrl);
  };


  /**
   * @method lookupOffsetX_
   * @private
   */
  PointDataMarker.prototype.lookupOffsetX_ = function() {
    var iconConfig = this.getIconConfig_();

    if (!iconConfig) {
      return this.get('offsetX');
    }

    return iconConfig.offsetX;
  };


  /**
   * @method lookupOffsetY_
   * @private
   */
  PointDataMarker.prototype.lookupOffsetY_ = function() {
    var iconConfig = this.getIconConfig_();

    if (!iconConfig) {
      return this.get('offsetY');
    }

    return iconConfig.offsetY;
  };


  /**
   * @method getIconConfig_
   * @private
   */
  PointDataMarker.prototype.getIconConfig_ = function() {
    var type = this.lookupType_();

    return this.iconLookup_[type];
  };


  /**
   * Override to set how the marker's
   * title attribute is parsed from
   * the data.
   *
   * @protected
   * @return {string}
   * @method lookupTitle_
   */
  PointDataMarker.prototype.lookupTitle_ = function() {
    return '';
  };


  /**
   * Lookup marker position
   * from data model.
   *
   * @protected
   * @return {aeris.maps.LatLon}
   * @method lookupPosition_
   */
  PointDataMarker.prototype.lookupPosition_ = function() {
    var loc = this.getDataAttribute('loc');

    // Fallback to current position
    if (!loc) {
      return this.get('position');
    }

    return [
      loc.lat,
      loc.long
    ];
  };


  return PointDataMarker;
});

define('aeris/maps/markers/earthquakemarker',[
  'aeris/util',
  'aeris/config',
  'aeris/maps/markers/pointdatamarker',
  'aeris/maps/markers/config/iconlookup'
], function(_, config, PointDataMarker, iconLookup) {
  /**
   * @publicApi
   * @class aeris.maps.markers.EarthquakeMarker
   * @extends aeris.maps.markers.PointDataMarker
   * @constructor
   */
  var EarthquakeMarker = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      url: config.get('assetPath') + 'quake_minor.png'
    }, opt_attrs);

    var options = _.extend({
      iconLookup: iconLookup.earthquake,
      typeAttribute: 'report.type'
    }, opt_options);


    PointDataMarker.call(this, attrs, options);

  };
  _.inherits(EarthquakeMarker, PointDataMarker);


  /**
   * @override
   * @method lookupTitle_
   * @protected
   */
  EarthquakeMarker.prototype.lookupTitle_ = function() {
    var mag = this.getDataAttribute('report.mag');
    return _.isUndefined(mag) ? 'Earthquake' :
      'Magnitute ' + mag.toFixed(1) + ' Earthquake.';
  };


  return _.expose(EarthquakeMarker, 'aeris.maps.markers.EarthquakeMarker');
});

define('aeris/maps/markercollections/earthquakemarkers',[
  'aeris/util',
  'aeris/maps/markercollections/pointdatamarkers',
  'aeris/api/collections/earthquakes',
  'aeris/maps/markers/earthquakemarker',
  'aeris/maps/markercollections/config/clusterstyles'
], function(_, PointDataMarkers, EarthquakeCollection, EarthquakeMarker, clusterStyles) {
  /**
   * @publicApi
   * @class aeris.maps.markercollections.EarthquakeMarkers
   * @extends aeris.maps.markercollections.PointDataMarkers
   *
   * @constructor
   */
  var EarthquakeMarkers = function(opt_markers, opt_options) {
    var options = _.extend({
      data: new EarthquakeCollection(),
      model: EarthquakeMarker,
      clusterStyles: clusterStyles.earthquake
    }, opt_options);

    PointDataMarkers.call(this, opt_markers, options);
  };
  _.inherits(EarthquakeMarkers, PointDataMarkers);


  return _.expose(EarthquakeMarkers, 'aeris.maps.markercollections.EarthquakeMarkers');
});

define('aeris/maps/markers/firemarker',[
  'aeris/util',
  'aeris/config',
  'aeris/maps/markers/pointdatamarker',
  'aeris/maps/markers/config/iconlookup'
], function(_, config, PointDataMarker, iconLookup) {
  /**
   * @publicApi
   * @class aeris.maps.markers.FireMarker
   * @extends aeris.maps.markers.PointDataMarker
   * @constructor
   */
  var FireMarker = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      url: iconLookup.fire.defaultStyles.url,
      offsetX: iconLookup.fire.defaultStyles.offsetX,
      offsetY: iconLookup.fire.defaultStyles.offsetY
    }, opt_attrs);

    PointDataMarker.call(this, attrs, opt_options);
  };
  _.inherits(FireMarker, PointDataMarker);


  /**
   * @method lookupTitle_
   */
  FireMarker.prototype.lookupTitle_ = function() {
    var cause = this.getDataAttribute('report.cause');

    return cause ? 'Fire caused by ' + cause : 'Fire';
  };


  return _.expose(FireMarker, 'aeris.maps.markers.FireMarker');
});

define('aeris/api/models/fire',[
  'aeris/util',
  'aeris/api/models/pointdata',
  'aeris/errors/apiresponseerror'
], function(_, PointData, ApiResponseError) {
  /**
   * @publicApi
   * @class aeris.api.models.Fire
   * @extends aeris.api.models.PointData
   *
   * @constructor
   * @override
   */
  var Fire = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      params: {
        from: 'today'
      }
    });

    PointData.call(this, opt_attrs, options);
  };
  _.inherits(Fire, PointData);


  /**
   * @method parse
   */
  Fire.prototype.parse = function(res) {
    var attrs = PointData.prototype.parse.apply(this, arguments);

    if (!res.report || !res.report.id) {
      throw new ApiResponseError('Missing fire report id');
    }

    attrs.id = res.report.id;

    return attrs;
  };


  return _.expose(Fire, 'aeris.api.models.Fire');
});

define('aeris/api/collections/fires',[
  'aeris/util',
  'aeris/api/collections/pointdatacollection',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/fire'
], function(_, PointDataCollection, AerisApiClientCollection, Fire) {
  /**
   * A representation of fire data from the
   * Aeris API 'fires' endpoint.
   *
   * @publicApi
   * @class aeris.api.collections.Fires
   * @extends aeris.api.collections.PointDataCollection
   *
   * @constructor
   * @override
   */
  var Fires = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      params: {
        limit: 500,
        query: [{
          property: 'type',
          value: 'L'
        }]
      },
      model: Fire,
      endpoint: 'fires',
      action: 'search',
      SourceCollectionType: PointDataCollection
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Fires, AerisApiClientCollection);


  return _.expose(Fires, 'aeris.api.collections.Fires');
});

define('aeris/maps/markercollections/firemarkers',[
  'aeris/util',
  'aeris/maps/markercollections/pointdatamarkers',
  'aeris/maps/markers/firemarker',
  'aeris/api/collections/fires',
  'aeris/maps/markercollections/config/clusterstyles'
], function(_, PointDataMarkers, FireMarker, FireCollection, clusterStyles) {
  /**
   * @publicApi
   * @class aeris.maps.markercollections.FireMarkers
   * @extends aeris.maps.markercollections.PointDataMarkers
   *
   * @constructor
   */
  var FireMarkers = function(opt_markers, opt_options) {
    var options = _.extend({
      model: FireMarker,
      data: new FireCollection(),
      clusterStyles: clusterStyles.fire
    }, opt_options);

    PointDataMarkers.call(this, opt_markers, options);
  };
  _.inherits(FireMarkers, PointDataMarkers);


  return _.expose(FireMarkers, 'aeris.maps.markercollections.FireMarkers');
});

define('aeris/api/models/lightning',[
  'aeris/util',
  'aeris/api/models/pointdata',
  'aeris/errors/apiresponseerror'
], function(_, PointData, ApiResponseError) {
  /**
   * Represents a lightning data response from the AerisApi
   *
   * @publicApi
   * @class aeris.api.models.Lightning
   * @extends aeris.api.models.PointData
   *
   * @constructor
   * @override
   */
  var Lightning = function(opt_attrs, opt_options) {
    PointData.call(this, opt_attrs, opt_options);
  };
  _.inherits(Lightning, PointData);


  /**
   * @method parse
   */
  Lightning.prototype.parse = function(attrs) {
    try {
      // The lightning endpoint does not provide an id
      // Create a unique identifier here, so that we
      // can determine which models from the server
      // we already have in a collection.
      attrs.id = '' + attrs.loc.lat + attrs.loc.long + attrs.obTimestamp;
    }
    catch (e) {
      if (e instanceof ReferenceError) {
        throw new ApiResponseError('Unable to determine Lightning id: ' + e.message);
      }
      else {
        throw e;
      }
    }

    return attrs;
  };


  return _.expose(Lightning, 'aeris.api.models.Lightning');
});

define('aeris/api/collections/lightning',[
  'aeris/util',
  'aeris/api/collections/pointdatacollection',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/lightning'
], function(_, PointDataCollection, AerisApiClientCollection, LightningModel) {
  /**
   * A representation of lighting data from the
   * Aeris API 'lightning' endpoint.
   *
   * @publicApi
   * @class aeris.api.collections.Lightning
   * @extends aeris.api.collections.PointDataCollection
   *
   * @constructor
   * @override
   */
  var Lightning = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      params: {},
      model: LightningModel,
      endpoint: 'lightning',
      action: 'within',
      SourceCollectionType: PointDataCollection
    });

    _.defaults(options.params, {
      limit: 250,

      // Sort to show newest lightning strikes first
      sort: 'dt:-1'
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Lightning, AerisApiClientCollection);


  return _.expose(Lightning, 'aeris.api.collections.Lightning');
});

define('aeris/maps/markers/lightningmarker',[
  'aeris/util',
  'aeris/config',
  'aeris/maps/markers/pointdatamarker',
  'aeris/maps/markers/config/iconlookup',
  'aeris/util/findclosest'
], function(_, config, PointDataMarker, iconLookup, findClosest) {
  var lightningStyles = iconLookup.lightning;
  /**
   * @publicApi
   * @class aeris.maps.markers.LightningMarker
   * @extends aeris.maps.markers.PointDataMarker
   * @constructor
   */
  var LightningMarker = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      iconLookup: lightningStyles
    });

    PointDataMarker.call(this, opt_attrs, options);
  };
  _.inherits(LightningMarker, PointDataMarker);


  /**
   * @method lookupType_
   * @private
   */
  LightningMarker.prototype.lookupType_ = function() {
    var styleTimes, lightningTimeAgo, lightningTimeAgo_minutes;
    styleTimes = Object.keys(this.iconLookup_).sort();

    if (!this.getDataAttribute('obTimestamp')) {
      return _.last(styleTimes);
    }

    lightningTimeAgo = Date.now() - this.getDataAttribute('obTimestamp') * 1000;
    lightningTimeAgo_minutes = lightningTimeAgo / (1000 * 60);


    var matchingStyleTime = styleTimes.reduceRight(function(matchingStyleTime, maxMinutesAgo) {
        maxMinutesAgo = parseInt(maxMinutesAgo);

        if (lightningTimeAgo_minutes <= maxMinutesAgo) {
          return maxMinutesAgo;
        }
        else {
          return matchingStyleTime;
        }
      }, styleTimes[0]);

    return parseInt(matchingStyleTime);
  };


  /**
   * @override
   * @method lookupTitle_
   * @protected
   */
  LightningMarker.prototype.lookupTitle_ = function() {
    return 'Lightning';
  };


  return _.expose(LightningMarker, 'aeris.maps.markers.LightningMarker');
});

define('aeris/maps/markercollections/lightningmarkers',[
  'aeris/util',
  'aeris/maps/markercollections/pointdatamarkers',
  'aeris/api/collections/lightning',
  'aeris/maps/markers/lightningmarker',
  'aeris/maps/markercollections/config/clusterstyles'
], function(_, PointDataMarkers, LightningCollection, LightningMarker, clusterStyles) {
  /**
   * @publicApi
   * @class aeris.maps.markercollections.LightningMarkers
   * @extends aeris.maps.markercollections.PointDataMarkers
   *
   * @constructor
   */
  var LightningMarkers = function(opt_markers, opt_options) {
    var options = _.extend({
      data: new LightningCollection(),
      model: LightningMarker,
      clusterStyles: clusterStyles.lightning
    }, opt_options);

    PointDataMarkers.call(this, opt_markers, options);
  };
  _.inherits(LightningMarkers, PointDataMarkers);


  return _.expose(LightningMarkers, 'aeris.maps.markercollections.LightningMarkers');
});

define('aeris/api/models/stormreport',[
  'aeris/util',
  'aeris/api/models/pointdata'
], function(_, PointData) {
  /**
   * @publicApi
   * @class aeris.api.models.StormReport
   * @extends aeris.api.models.PointData
   *
   * @constructor
   * @override
   */
  var StormReport = function(opt_attrs, opt_options) {
    PointData.call(this, opt_attrs, opt_options);

    // Update generated 'stormtypes' attr
    // Types are returned as a space-separated list.
    // --> converts to array of types
    this.listenTo(this, {
      'change:report': function() {
        var types = res.report.type.split(' ');
        this.set('stormtypes', types);
      }
    });
  };
  _.inherits(StormReport, PointData);


  /**
   * @method parse
   */
  StormReport.prototype.parse = function(res) {
    var attrs = PointData.prototype.parse.apply(this, arguments);

    attrs.id = res.id;

    // Types are returned as a space-separated list.
    attrs.stormtypes = res.report.type.split(' ');

    return attrs;
  };


  /**
   * @method testFilter
   */
  StormReport.prototype.testFilter = function(filter) {
    return _.contains(this.get('stormtypes'), filter);
  };


  return _.expose(StormReport, 'aeris.api.models.StormReport');
});

/**
 * FYI: Map of codes to storm type.
    {
      'T': 'tornado', // tornado
      'C': 'tornado', // funnel cloud
      'W': 'tornado', // water spout

      'O': 'highwind', // non tstorm wind damage
      'D': 'highwind', // tstorm wind damage
      'N': 'highwind', // high wind speed
      'G': 'highwind', // high wind gust
      'A': 'highwind', // high sustained winds
      'M': 'highwind', // marine thunderstorm winds

      'H': 'hail', // hail

      'E': 'flood', // flood
      'F': 'flood', // flash flood

      'R': 'rain', // heavy rain
      'L': 'lightning', // lightning

      '4': 'highsurf', // tides
      'P': 'highsurf', // rip currents

      '2': 'dust', // dust storm
      'A': 'avalanche', // avalanche
      'U': 'wildfire', // wildfire
      'S': 'snow' // snow
    };
 */
;
define('aeris/api/collections/stormreports',[
  'aeris/util',
  'aeris/api/collections/pointdatacollection',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/stormreport',
  'aeris/datehelper'
], function(_, PointDataCollection, AerisApiClientCollection, StormReport, DateHelper) {
  /**
   * A representation of storm report data from the
   * Aeris API 'stormreports' endpoint.
   *
   * @publicApi
   * @class aeris.api.collections.StormReports
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
   * @override
   */
  var StormReports = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      params: {
        from: new DateHelper().addDays(-2).getDate(),
        to: new Date(),
        limit: 100
      },
      endpoint: 'stormreports',
      action: 'within',
      model: StormReport,
      SourceCollectionType: PointDataCollection
    });

    AerisApiClientCollection.call(this, opt_models, options);

    /**
     * @property sourceCollection_
     * @type {aeris.api.collections.PointDataCollection}
     */
  };
  _.inherits(StormReports, AerisApiClientCollection);


  return _.expose(StormReports, 'aeris.api.collections.StormReports');
});

define('aeris/maps/markers/stormreportmarker',[
  'aeris/util',
  'aeris/config',
  'aeris/maps/markers/pointdatamarker',
  'aeris/maps/markers/config/iconlookup'
], function(_, config, PointDataMarker, iconLookup) {
  /**
   * @publicApi
   * @class aeris.maps.markers.StormReportMarker
   * @extends aeris.maps.markers.PointDataMarker
   * @constructor
   */
  var StormReportMarker = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      url: config.get('assetPath') + 'marker_yellow.png'
    }, opt_attrs);

    var options = _.extend({
      iconLookup: iconLookup.stormReport,
      typeAttribute: 'stormtypes'
    }, opt_options);

    PointDataMarker.call(this, attrs, options);
  };
  _.inherits(StormReportMarker, PointDataMarker);


  /**
   * @override
   * @method lookupTitle_
   * @protected
   */
  StormReportMarker.prototype.lookupTitle_ = function() {
    var type = this.getDataAttribute('report.type');
    var name = this.getDataAttribute('report.name');

    if (!type || !name) {
      return this.get('title');
    }

    // Capitalize type
    type = type.charAt(0).toUpperCase() + type.slice(1);

    return (type && name) ? type + ': ' + name :
      'Storm report';
  };


  return _.expose(StormReportMarker, 'aeris.maps.markers.StormReportMarker');
});

define('aeris/maps/markercollections/stormreportmarkers',[
  'aeris/util',
  'aeris/maps/markercollections/pointdatamarkers',
  'aeris/api/collections/stormreports',
  'aeris/maps/markers/stormreportmarker',
  'aeris/maps/markercollections/config/clusterstyles'
], function(_, PointDataMarkers, StormReportCollection, StormReportMarker, clusterStyles) {
  /**
   * @publicApi
   * @class aeris.maps.markercollections.StormReportMarkers
   * @extends aeris.maps.markercollections.PointDataMarkers
   *
   * @constructor
   */
  var StormReportMarkers = function(opt_markers, opt_options) {
    var options = _.extend({
      data: new StormReportCollection(),
      model: StormReportMarker,
      clusterStyles: clusterStyles.stormReport
    }, opt_options);

    PointDataMarkers.call(this, opt_markers, options);
  };
  _.inherits(StormReportMarkers, PointDataMarkers);


  return _.expose(StormReportMarkers, 'aeris.maps.markercollections.StormReportMarkers');
});

define('aeris/maps/strategy/markers/stormcells',[
  'aeris/util',
  'aeris/maps/abstractstrategy',
  'leaflet',
  'aeris/maps/strategy/util'
], function(_, AbstractStrategy, Leaflet, MapUtil) {
  /** @class StormCells */
  var StormCells = function(stormCellsMapObject) {
    AbstractStrategy.call(this, stormCellsMapObject);
  };
  _.inherits(StormCells, AbstractStrategy);


  StormCells.prototype.createView_ = function() {
    return new Leaflet.geoJson(this.object_.toGeoJson(), {
      pointToLayer: function(feature, latLng) {
        return new Leaflet.CircleMarker(latLng);
      }.bind(this),
      onEachFeature: this.initializeFeature_.bind(this)
    });
  };

  StormCells.prototype.setMap = function(map) {
    AbstractStrategy.prototype.setMap.call(this, map);

    this.view_.addTo(this.mapView_);
  };

  StormCells.prototype.beforeRemove_ = function() {
    this.mapView_.removeLayer(this.view_);
  };

  StormCells.prototype.initializeFeature_ = function(feature, layer) {
    var EventTrigger = function(eventType) {
      return function(evt) {
        this.object_.trigger(eventType, MapUtil.toAerisLatLon(evt.latlng), this.object_);
      };
    };

    // Style the layers
    var styles = this.object_.getStyle(feature.properties);
    this.setLayerStyle(layer, styles);

    // Proxy events
    layer.on({
      click: EventTrigger('click').bind(this),
      mouseover: EventTrigger('mouseover').bind(this),
      mouseout: EventTrigger('mouseout').bind(this)
    });

    // Handle hover events
    layer.on({
      mouseover: function(evt) {
        this.setLayerStyle(layer, {
          cell: styles.cell.hover,
          cone: styles.cone.hover,
          line: styles.line.hover
        });

        if (!Leaflet.Browser.ie && !Leaflet.Browser.opera) {
          // Apparently, this doesn't work in IE or Opera...
          layer.bringToFront();
        }
      }.bind(this),
      mouseout: function(evt) {
        this.setLayerStyle(layer, styles);
      }.bind(this)
    });
  };

  StormCells.prototype.setLayerStyle = function(layer, objectStyles) {
    layer.getLayers().forEach(function(layer) {
      if (layer instanceof Leaflet.CircleMarker) {
        layer.setStyle(objectStyles.cell);
      }
      else if (layer instanceof Leaflet.Polygon) {
        layer.setStyle(objectStyles.cone);
      }
      else if (layer instanceof Leaflet.Polyline) {
        layer.setStyle(objectStyles.line);
      }
    }, this);
  };

  return StormCells;
});

define('aeris/maps/markers/stormcellmarker',[
  'aeris/util',
  'aeris/maps/extensions/mapextensionobject',
  'aeris/maps/strategy/markers/stormcells'
], function(_, MapExtensionObject, StormCellStrategy) {
  /** @class StormCellMarker */
  var StormCellMarker = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      strategy: StormCellStrategy,
      style: this.getStyleDefault_.bind(this)
    });

    this.getStyle = _.isFunction(options.style) ? options.style : _.constant(options.style);

    MapExtensionObject.call(this, opt_attrs, options);
  };
  _.inherits(StormCellMarker, MapExtensionObject);


  StormCellMarker.prototype.toGeoJson = function() {
    return this.getData().toJSON();
  };

  StormCellMarker.prototype.getStyleDefault_ = function(properties) {
    var styles = {
      cell: {
        radius: 4,
        fillColor: '#3bca24',
        color: '#000',
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8,
        hover: {
          radius: 7,
          fillOpacity: 1
        }
      },
      cone: {
        stroke: true,
        color: '#030303',
        weight: 1,
        opacity: 0.8,
        fillColor: '#f66',
        fillOpacity: 0.2,
        hover: {
          weight: 2,
          fillOpacity: 0.8,
          fillColor: '#f66'
        }
      },
      line: {
        stroke: true,
        color: '#030303',
        weight: 1,
        opacity: 0.8,
        hover: {}
      }
    };
    var typeStyles = {
      tornado: {
        radius: 5,
          fillColor: 'red'
      },
      hail: {
        radius: 5,
        fillColor: 'yellow'
      },
      rotating: {
        radius: 5,
        fillColor: 'orange'
      }
    };
    var type = _.path('traits.type', properties);

    if (typeStyles[type]) {
      _.extend(styles.cell, typeStyles[type]);
    }

    return styles;
  };

  return StormCellMarker;
});

define('aeris/api/models/geojsonfeature',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /** @class GeoJsonFeature */
  var GeoJsonFeature = function(opt_attrs, opt_options) {
    AerisApiModel.call(this, opt_attrs, opt_options);
  };
  _.inherits(GeoJsonFeature, AerisApiModel);

  GeoJsonFeature.prototype.parse = function(attrs) {
    attrs.id = attrs.properties.id;

    return attrs;
  };

  return GeoJsonFeature;
});

define('aeris/api/collections/geojsonfeaturecollection',[
  'aeris/util',
  'aeris/api/collections/aerisapicollection',
  'aeris/api/models/geojsonfeature',
  'aeris/promise',
  'jquery'
], function(_, AerisApiCollection, GeoJsonFeature, Promise, $) {
  /** @class GeoJsonFeatureCollection */
  var GeoJsonFeatureCollection = function(geoJson, opt_options) {
    var models = geoJson ? this.parse(geoJson) : null;
    var options = _.defaults(opt_options || {}, {
      model: GeoJsonFeature
    });

    options.params = _.extend({}, options.params || {}, {
      format: 'geojson'
    });

    AerisApiCollection.call(this, models, options);
  };
  _.inherits(GeoJsonFeatureCollection, AerisApiCollection);

  GeoJsonFeatureCollection.prototype.parse = function(geoJson) {
    var models = geoJson.features;
    return AerisApiCollection.prototype.parse.call(this, models);
  };

  GeoJsonFeatureCollection.prototype.toGeoJson = function() {
    return {
      type: 'FeatureCollection',
      features: this.toJSON()
    };
  };

  GeoJsonFeatureCollection.prototype.isSuccessResponse_ = function(res) {
    return !res.error;
  };

  return _.expose(GeoJsonFeatureCollection, 'aeris.api.collections.GeoJsonFeatureCollection');
});

define('aeris/maps/markercollections/stormcellmarkers',[
  'aeris/util',
  'aeris/maps/markercollections/pointdatamarkers',
  'aeris/maps/markers/stormcellmarker',
  'aeris/api/collections/geojsonfeaturecollection'
], function(_, PointDataMarkers, StormCellMarker, GeoJsonFeatureCollection) {
  /** @class StormCellMarkers */
  var StormCellMarkers = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      data: new GeoJsonFeatureCollection(null, {
        endpoint: 'stormcells',
        action: 'within',
        params: {
          p: ':auto',
          radius: '3000mi',
          limit: 1000,
          fields: [
            'id',
            'place',
            'loc',
            'ob',
            'forecast',
            'traits'
          ],
          sort: 'tor:-1,mda:-1,hail:-1'
        }
      }),
      model: StormCellMarker,
      strategy: _.noop,
      // StormCells do not yet support clustering
      clusterStrategy: null,
      cluster: false
    });


    PointDataMarkers.call(this, opt_models, options);
  };
  _.inherits(StormCellMarkers, PointDataMarkers);


  StormCellMarkers.prototype.toGeoJson = function() {
    return this.data_.toGeoJson();
  };

  StormCellMarkers.prototype.startClustering = function() {
    throw new Error('StormCellMarkers do not currently support clustering');
  };

  return StormCellMarkers;
});

define('aeris/packages/markers',[
  'aeris/maps/markers/marker',
  'aeris/maps/markercollections/earthquakemarkers',
  'aeris/maps/markercollections/firemarkers',
  'aeris/maps/markercollections/lightningmarkers',
  'aeris/maps/markercollections/stormreportmarkers',
  'aeris/maps/markercollections/stormcellmarkers'
], function() {});

define('aeris/errors/missingapikeyerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.MissingApiKeysError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'MissingApiKeyError'
  });
});

define('aeris/errors/timeouterror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.TimeoutError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'TimeoutError'
  });
});

define('aeris/errors/unsupportedfeatureerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * An attempt has been made to use a feature
   * which is not currently supported by the Aeris.js
   * library.
   *
   * @class aeris.errors.UnsupportedFeatureError
   * @extends aeris.errors.AbstractError
   */
  return new ErrorTypeFactory({
    name: 'UnsupportedFeatureError'
  });
});

define('aeris/api/getjson',['underscore', 'aeris/promise'], function(_, Promise) {
  return function getJson(url) {
    var promise = new Promise();

    var request = new XMLHttpRequest();
    request.open('GET', url, true);

    request.onload = function() {
      if (request.status >= 200 && request.status < 400) {
        try { var json = JSON.parse(request.responseText); }
        catch (err) { return promise.reject(err); }

        return promise.resolve(json);
      } else {
        var err = _.extend(new Error('Request to ' + url + ' returned an HTTP error code'), {
          xhr: request
        });
        return promise.reject(err);
      }
    };

    request.onerror = promise.reject;

    request.send();

    return promise;
  };
});

define('aeris/maps/layers/config/zindex',{
  Radar: 70,

  SatelliteGlobal: 60,
  SatelliteVisible: 60,
  Satellite: 60,

  Advisories: 45,

  Temps: 40,
  WindChill: 40,
  Winds: 40,
  HeatIndex: 40,
  Humidity: 40,
  DewPoints: 40,

  SnowDepth: 30,


  Chlorophyll: 10,
  SeaSurfaceTemps: 10
});

define('aeris/maps/strategy/layers/aeristile',[
  'aeris/util',
  'aeris/maps/strategy/layers/tile'
], function(_, Tile) {
  /**
   * Rendering strategy for Aeris Tiles.
   *
   * @class aeris.maps.leaflet.layers.AerisTile
   * @extends aeris.maps.leaflet.layers.Tile
   *
   * @constructor
  */
  var AerisTileStrategy = function(mapObject) {
    Tile.call(this, mapObject);
  };
  _.inherits(AerisTileStrategy, Tile);


  /**
   * @method getTileUrl_
   */
  AerisTileStrategy.prototype.getTileUrl_ = function() {
    var url = Tile.prototype.getTileUrl_.call(this);

    return url.replace('{t}', this.object_.getAerisTimeString());
  };


  return AerisTileStrategy;
});

define('aeris/util/timestring',[], function() {
  return {
    fromDate: function(date) {
      // AMP time format is
      // YYYYMMDDHHmmss
      return [date.getFullYear()]
        .concat(
          [
            date.getUTCMonth() + 1,
            date.getUTCDate(),
            date.getUTCHours(),
            date.getUTCMinutes(),
            date.getUTCSeconds()
          ].map(function(str) {
            return padLeft(str, 2);
          })
        )
        .join('');
    }
  };
});

// http://stackoverflow.com/a/5367656/830030
function padLeft(number, maxLen, padStr) {
  var padLength = maxLen - String(number).length + 1;
  padStr || (padStr = '0');

  return new Array(padLength)
      .join(padStr || '0') + number;
}
;
define('aeris/maps/layers/aeristile',[
  'aeris/util',
  'aeris/config',
  'aeris/promise',
  'aeris/errors/validationerror',
  'aeris/errors/missingapikeyerror',
  'aeris/errors/timeouterror',
  'aeris/errors/unsupportedfeatureerror',
  'aeris/maps/layers/abstracttile',
  'aeris/api/getjson',
  'aeris/maps/layers/config/zindex',
  'aeris/maps/strategy/layers/aeristile',
  'aeris/util/timestring'
], function(_, aerisConfig, Promise, ValidationError, MissingApiKeyError, TimeoutError, UnsupportedFeatureError, BaseTile, getJson, zIndexConfig, AerisTileStrategy, timeString) {
  /**
   * Representation of Aeris Interactive Tile layer.
   *
   * @constructor
   * @class aeris.maps.layers.AerisTile
   * @extends aeris.maps.layers.AbstractTile
   */
  var AerisTile = function(opt_attrs, opt_options) {
    var options = _.extend({
      strategy: AerisTileStrategy,
      validate: true
    }, opt_options);

    var attrs = _.defaults(opt_attrs || {}, {
      subdomains: ['1', '2', '3', '4'],
      server: '//maps{d}.aerisapi.com',
      maxZoom: 27,
      minZoom: 1,

      /**
       * Tile's timestamp.
       * Defaults to 0.
       * Note that request to the AI Tiles API
       * at time '0' will return the latest available tile.
       *
       * @attribute time
       * @type {Date}
       */
      time: new Date(0),

      /**
       * Interactive tile type.
       *
       * @attribute tileType
       * @type {string}
       * @abstract
       */
      tileType: '',


      /**
       * The tile time index to use for displaying the layer.
       *
       * @type {number}
       */
      timeIndex: 0,


      /**
       * The layer's animation step.
       *
       * @type {number}
       */
      animationStep: 1,


      /**
       * Interval at which to update the tile.
       *
       * @attribute autoUpdateInterval
       * @type {number} Milliseconds.
       */
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT,


      /**
       * Whether to auto-update the tile.
       * Auto-updating mean that every this.autoUpdateInterval
       * milliseconds, the tile's time attribute will reset.
       */
      autoUpdate: true,

      /**
       * Aeris API client_id
       *
       * @attribute apiId
       * @type {String}
       */
      apiId: aerisConfig.get('apiId'),

      /**
       * Aeris API client_secret
       *
       * @attribute apiSecret
       * @type {String}
       */
      apiSecret: aerisConfig.get('apiSecret')
    });

    _.defaults(attrs, {
      zIndex: zIndexConfig[attrs.name] || 1,

      /**
       * The type of Aeris Interactive tile
       * to use when the tile's time is set to a future date.
       *
       * @attribute futureTileType
       */
      futureTileType: attrs.tileType
    });


    /**
     * A reference to the timer
     * created with window.setInterval,
     * used for autoUpdating.
     *
     * @type {number}
     * @private
     * @property autoUpdateIntervalTimer_
     */
    this.autoUpdateIntervalTimer_;


    /**
     * Have the tile images for this layer
     * have been loaded?
     *
     * @type {Boolean}
     * @private
     */
    this.loaded_ = false;


    BaseTile.call(this, attrs, options);


    this.bindToApiKeys_();


    /**
     * The tile has automatically updated
     * to the most current time.
     *
     * @event autoUpdate
     */
  };
  _.inherits(AerisTile, BaseTile);


  /**
   * @method initialize
   * @protected
   */
  AerisTile.prototype.initialize = function() {
    var setAutoUpdate = (function() {
      if (this.get('autoUpdate')) {
        this.startAutoUpdate_();
      }
      else {
        this.stopAutoUpdate_();
      }
    }).bind(this);
    setAutoUpdate();      // Setup autoUpdate event on init

    // When autoUpdate property is toggled
    // start or stop auto-updating.
    this.on({
      'change:autoUpdate': setAutoUpdate,
      'change:autoUpdateInterval': function() {
        this.stopAutoUpdate_();
        this.startAutoUpdate_();
      }
    }, this);


    BaseTile.prototype.initialize.apply(this, arguments);
  };


  AerisTile.prototype.bindToApiKeys_ = function() {
    this.listenTo(aerisConfig, 'change:apiId change:apiSecret', function() {
      this.set({
        apiId: this.get('apiId') || aerisConfig.get('apiId'),
        apiSecret: this.get('apiSecret') || aerisConfig.get('apiSecret')
      });
    });
  };


  AerisTile.prototype.startAutoUpdate_ = function() {
    this.autoUpdateIntervalTimer_ = window.setInterval(function() {
      this.set('time', new Date(0));
      this.trigger('autoUpdate');
    }.bind(this), this.get('autoUpdateInterval'));
  };


  AerisTile.prototype.stopAutoUpdate_ = function() {
    if (!this.autoUpdateIntervalTimer_) {
      return;
    }

    window.clearInterval(this.autoUpdateIntervalTimer_);
  };


  /**
   * @method validate
   */
  AerisTile.prototype.validate = function(attrs) {
    var isFutureTile;

    if (!_.isString(attrs.tileType)) {
      return new ValidationError('tileType', 'not a valid string');
    }
    if (!_.isNumber(attrs.autoUpdateInterval)) {
      return new ValidationError('autoUpdateInterval', 'not a valid number');
    }
    if (attrs.minZoom < 1) {
      return new ValidationError('minZoom for Aeris Interactive tiles must be ' +
        'more than 0');
    }

    isFutureTile = attrs.time > new Date();
    if (isFutureTile && attrs.autoUpdate) {
      return new UnsupportedFeatureError('Auto update is not currently supported by for future tiles.' +
        ' Turn off auto update (tile.set(\'autoUpdate\', false) before using future tiles.');
    }

    return BaseTile.prototype.validate.apply(this, arguments);
  };


  /**
   * Update intervals used by the Aeris API
   * @static
   */
  AerisTile.updateIntervals = {
    RADAR: 1000 * 60 * 6,         // every 6 minutes
    CURRENT: 1000 * 60 * 60,      // hourly
    MODIS: 1000 * 60 * 60 * 24,   // daily
    SATELLITE: 1000 * 60 * 30,    // every 30 minutes
    ADVISORIES: 1000 * 60 * 3     // every 3 minutes
  };


  /**
   * @method getUrl
   */
  AerisTile.prototype.getUrl = function() {
    this.ensureApiKeys_();

    return this.get('server') + '/' +
      this.get('apiId') + '_' +
      this.get('apiSecret') +
      '/' + this.getTileTypeEndpoint_() +
      '/{z}/{x}/{y}/{t}.png';
  };


  /**
   * @throws {aeris.errors.MissingApiKeysError}
   * @private
   * @method ensureApiKeys_
   */
  AerisTile.prototype.ensureApiKeys_ = function() {
    this.set({
      apiId: this.get('apiId') || aerisConfig.get('apiId'),
      apiSecret: this.get('apiSecret') || aerisConfig.get('apiSecret')
    });

    if (!this.get('apiId') || !this.get('apiSecret')) {
      throw new MissingApiKeyError('Aeris API id and secret required to render ' +
        'interactive tiles.');
    }
  };


  /**
   * @return {number} UNIX Timestamp.
   * @method getTimestamp
   */
  AerisTile.prototype.getTimestamp = function() {
    return this.get('time').getTime();
  };


  /**
   * Get's the layer's time,
   * formatted for the Aeris API.
   *
   * @return {string} Format: [year][month][date][hours][minutes][seconds].
   * @method getAerisTimeString
   */
  AerisTile.prototype.getAerisTimeString = function() {
    var time = this.get('time');

    // Aeris accepts 0, -1, -2, or -3
    // As 'X' times before now.
    // ie '0.png' returns the most recent tile
    if (time.getTime() <= 0) {
      return time.getTime();
    }

    return timeString.fromDate(time);
  };


  /**
   * Retrieve a list of timestamps for which
   * tile images are available on the AerisAPI server.
   *
   * @return {aeris.Promise} Resolves with arrary of timestamps.
   * @throws {aeris.errors.MissingAPIKeyError}
   * @method loadTileTimes
   */
  AerisTile.prototype.loadTileTimes = function() {
    var promiseToLoadAllTimes = new Promise();
    var pastTimesEndpoint = this.get('tileType');
    var futureTimesEndpoint = this.get('futureTileType');
    var isFutureTimesAvailable = pastTimesEndpoint !== futureTimesEndpoint;

    var loadPromises = [];

    loadPromises.push(this.loadTileTimesForEndpoint_(pastTimesEndpoint));

    if (isFutureTimesAvailable) {
      loadPromises.push(this.loadTileTimesForEndpoint_(futureTimesEndpoint));
    }

    Promise.when(loadPromises).
      done(function(currTimesArgs, futureTimesArgs) {
        var currentTimes = currTimesArgs[0];
        var futureTimes = (futureTimesArgs ? futureTimesArgs[0] : [])
          // Remove any future times that are actually from the past
          // Otherwise, animations will attempt to load those times using the past `tileType`
          .filter(function(time) {
            return time > Date.now();
          });

        promiseToLoadAllTimes.resolve(currentTimes.concat(futureTimes));
      }).
      fail(promiseToLoadAllTimes.reject.bind(promiseToLoadAllTimes));

    return promiseToLoadAllTimes;
  };


  /**
   * @method loadTileTimesForEndpoint_
   * @private
   * @param {string} endpoint
   * @return {aeris.Promise}
   */
  AerisTile.prototype.loadTileTimesForEndpoint_ = function(endpoint) {
    var promiseToLoadTimes = new Promise();
    var url = this.createTileTimesUrlForEndpoint_(endpoint);

    this.ensureApiKeys_();

    getJson(url)
      .done(function(res) {
        var times;

        if (!res.files) {
          promiseToLoadTimes.reject(new Error('Failed to load tile times: no time data was returned.'));
        }

        times = this.parseTileTimes_(res);

        promiseToLoadTimes.resolve(times);
      }.bind(this))
      .fail(function(err) {
        if (err.xhr && err.xhr.status === 401) {
          console.warn('Client does not have access to tile times for ' + endpoint);
          return promiseToLoadTimes.resolve([]);
        }
        promiseToLoadTimes.reject(err);
      });

    return promiseToLoadTimes;
  };


  /**
   * @param {aeris.Promise} promise
   * @param {number} timeout
   * @param {string} message
   * @private
   * @method rejectAfterTimeout_
   */
  AerisTile.prototype.rejectAfterTimeout_ = function(promise, timeout, message) {
    _.delay(function() {
      if (promise.getState() === 'pending') {
        promise.reject(new TimeoutError(message));
      }
    }.bind(this), timeout);
  };


  /**
   * @return {string}
   * @private
   * @method createTileTimesUrl_
   */
  AerisTile.prototype.createTileTimesUrl_ = function() {
    var pastTimesEndpoint = this.get('tileType');

    return this.createTileTimesUrlForEndpoint_(pastTimesEndpoint);
  };


  /**
   * @method createFutureTileTimesUrl_
   * @private
   */
  AerisTile.prototype.createFutureTileTimesUrl_ = function() {
    var futureTimesEndpoint = this.get('futureTileType');

    return this.createTileTimesUrlForEndpoint_(futureTimesEndpoint);
  };


  /**
   * @method createTileTimesUrlForEndpoint_
   * @private
   * @param {string} endpoint Tile type endpoint.
   */
  AerisTile.prototype.createTileTimesUrlForEndpoint_ = function(endpoint) {
    var urlPattern = '{server}/{client_id}_{client_secret}/{tileType}.json';
    var server = this.get('server').replace('{d}', '');

    return urlPattern.
      replace('{server}', server).
      replace('{client_id}', this.get('apiId')).
      replace('{client_secret}', this.get('apiSecret')).
      replace('{tileType}', endpoint);
  };


  /**
   * @param {Object} res Aeris Tile Times API response object.
   * @return {Array.<number>} Array of JS formatted timestamps.
   * @private
   * @method parseTileTimes_
   */
  AerisTile.prototype.parseTileTimes_ = function(res) {
    return res.files.map(function(time) {
      // Convert UNIX timestamp (seconds)
      // to JS timestamp (milliseconds)
      return time.timestamp * 1000;
    });
  };


  /**
   * @method getTileTypeEndpoint_
   * @private
   */
  AerisTile.prototype.getTileTypeEndpoint_ = function() {
    var isFutureTile = this.get('time') > new Date();

    return isFutureTile ? this.get('futureTileType') : this.get('tileType');
  };


  return _.expose(AerisTile, 'aeris.maps.layers.AerisTile');
});

define('aeris/maps/layers/advisories',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.Advisories
   * @extends aeris.maps.layers.AerisTile
   */
  var Advisories = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Advisories',
      tileType: 'alerts',
      autoUpdateInterval: AerisTile.updateIntervals.ADVISORIES
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(Advisories, AerisTile);


  return _.expose(Advisories, 'aeris.maps.layers.Advisories');
});

define('aeris/maps/layers/chlorophyll',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * Representation of Aeris Sea Surface Temperatures layer.
   *
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.Chlorophyll
   * @extends aeris.maps.layers.AerisTile
   */
  var Chlorophyll = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Chlorophyll',
      tileType: 'modis-chlo'
    }, opt_attrs);

    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(Chlorophyll, AerisTile);


  return _.expose(Chlorophyll, 'aeris.maps.layers.Chlorophyll');
});

define('aeris/maps/layers/dewpoints',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.DewPoints
   * @extends aeris.maps.layers.AerisTile
   */
  var DewPoints = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'DewPoints',
      tileType: 'dew-points',
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(DewPoints, AerisTile);


  return _.expose(DewPoints, 'aeris.maps.layers.DewPoints');
});

define('aeris/maps/layers/heatindex',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.HeatIndex
   * @extends aeris.maps.layers.AerisTile
   */
  var HeatIndex = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'HeatIndex',
      tileType: 'heat-index',
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(HeatIndex, AerisTile);


  return _.expose(HeatIndex, 'aeris.maps.layers.HeatIndex');
});

define('aeris/maps/layers/humidity',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.Humidity
   * @extends aeris.maps.layers.AerisTile
   */
  var Humidity = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Humidity',
      tileType: 'humidity',
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(Humidity, AerisTile);


  return _.expose(Humidity, 'aeris.maps.layers.Humidity');
});

define('aeris/maps/layers/lightningstrikedensity',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * Representation of Aeris Lightning Strike Density layer.
   *
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.LightningStrikeDensity
   * @extends aeris.maps.layers.AerisTile
   */
  var LightningStrikeDensity = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Lightning Strike Density',
      tileType: 'lightning-strike-density',
      autoUpdateInterval: AerisTile.updateIntervals.SATELLITE
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };
  _.inherits(LightningStrikeDensity, AerisTile);




  return _.expose(LightningStrikeDensity, 'aeris.maps.layers.LightningStrikeDensity');

});

define('aeris/maps/layers/precip',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * Precipitation layer.
   *
   * @class aeris.maps.layers.Precip
   * @extends aeris.maps.layers.AerisTile
   * @publicApi
   *
   * @constructor
   */
  var Precip = function(opt_attrs, opt_options) {
    var attrs = _.defaults(opt_attrs || {}, {
      name: 'Precip',
      tileType: 'precip',
      futureTileType: 'frain',
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT
    });

    AerisTile.call(this, attrs, opt_options);
  };
  _.inherits(Precip, AerisTile);


  return _.expose(Precip, 'aeris.maps.layers.Precip');
});

define('aeris/maps/layers/qpf',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * Representation of Aeris QPF (Quantitative Precipitation Forecast) layer.
   *
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.QPF
   * @extends aeris.maps.layers.AerisTile
   */
  var QPF = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'QPF',
      tileType: 'fqpf',
      futureTileType: 'fqpf',
      autoUpdateInterval: 1000 * 60 * 60 * 6    // every 6 hours
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };
  _.inherits(QPF, AerisTile);


  return _.expose(QPF, 'aeris.maps.layers.QPF');
});

define('aeris/maps/layers/radar',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.Radar
   * @extends aeris.maps.layers.AerisTile
   */
  var Radar = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Radar',
      tileType: 'radar',
      futureTileType: 'frad',
      autoUpdateInterval: AerisTile.updateIntervals.RADAR
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };
  _.inherits(Radar, AerisTile);


  return _.expose(Radar, 'aeris.maps.layers.Radar');
});

define('aeris/maps/layers/satellite',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.Satellite
   * @extends aeris.maps.layers.AerisTile
   */
  var Satellite = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Satellite',
      tileType: 'satellite',
      futureTileType: 'fsat',
      autoUpdateInterval: AerisTile.updateIntervals.SATELLITE
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(Satellite, AerisTile);


  return _.expose(Satellite, 'aeris.maps.layers.Satellite');
});

define('aeris/maps/layers/satelliteglobal',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.SatelliteGlobal
   * @extends aeris.maps.layers.AerisTile
   */
  var SatelliteGlobal = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'SatelliteGlobal',
      tileType: 'sat-global',
      futureTileType: 'fsat',
      autoUpdateInterval: AerisTile.updateIntervals.SATELLITE
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(SatelliteGlobal, AerisTile);


  return _.expose(SatelliteGlobal, 'aeris.maps.layers.SatelliteGlobal');
});

define('aeris/maps/layers/satellitevisible',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.SatelliteVisible
   * @extends aeris.maps.layers.AerisTile
   */
  var SatelliteVisible = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'SatelliteVisible',
      tileType: 'sat-vis-hires',
      autoUpdateInterval: AerisTile.updateIntervals.SATELLITE
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(SatelliteVisible, AerisTile);


  return _.expose(SatelliteVisible, 'aeris.maps.layers.SatelliteVisible');
});

define('aeris/maps/layers/seasurfacetemps',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * Representation of Aeris Sea Surface Temperatures layer.
   *
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.SeaSurfaceTemps
   * @extends aeris.maps.layers.AerisTile
   */
  var SeaSurfaceTemps = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'SeaSurfaceTemps',
      tileType: 'modis-sst'
    }, opt_attrs);

    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(SeaSurfaceTemps, AerisTile);


  return _.expose(SeaSurfaceTemps, 'aeris.maps.layers.SeaSurfaceTemps');
});

define('aeris/maps/layers/snowdepth',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.SnowDepth
   * @extends aeris.maps.layers.AerisTile
   */
  var SnowDepth = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'SnowDepth',
      tileType: 'snowdepth',
      autoUpdateInterval: AerisTile.updateIntervals.MODIS
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };
  _.inherits(SnowDepth, AerisTile);


  return _.expose(SnowDepth, 'aeris.maps.layers.SnowDepth');
});

define('aeris/maps/layers/snowfallaccum',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.SnowFallAccum
   * @extends aeris.maps.layers.AerisTile
   */
  var SnowFallAccum = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'SnowFallAccum',
      tileType: 'fsnow',
      futureTileType: 'fsnow',
      autoUpdateInterval: AerisTile.updateIntervals.MODIS
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };
  _.inherits(SnowFallAccum, AerisTile);


  return _.expose(SnowFallAccum, 'aeris.maps.layers.SnowFallAccum');
});

define('aeris/maps/layers/temps',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.Temps
   * @extends aeris.maps.layers.AerisTile
   */
  var Temps = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Temps',
      tileType: 'temperatures',
      futureTileType: 'ftemps',
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(Temps, AerisTile);


  return _.expose(Temps, 'aeris.maps.layers.Temps');
});

define('aeris/maps/layers/windchill',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.WindChill
   * @extends aeris.maps.layers.AerisTile
   */
  var WindChill = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'WindChill',
      tileType: 'windchill',
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(WindChill, AerisTile);


  return _.expose(WindChill, 'aeris.maps.layers.WindChill');
});

define('aeris/maps/layers/winds',[
  'aeris/util',
  'aeris/maps/layers/aeristile'
], function(_, AerisTile) {
  /**
   * @constructor
   * @publicApi
   * @class aeris.maps.layers.Winds
   * @extends aeris.maps.layers.AerisTile
   */
  var Winds = function(opt_attrs, opt_options) {
    var attrs = _.extend({
      name: 'Winds',
      tileType: 'winds',
      futureTileType: 'fwinds',
      autoUpdateInterval: AerisTile.updateIntervals.CURRENT
    }, opt_attrs);


    AerisTile.call(this, attrs, opt_options);
  };

  // Inherit from AerisTile
  _.inherits(Winds, AerisTile);


  return _.expose(Winds, 'aeris.maps.layers.Winds');
});

define('aeris/packages/layers/tilelayers',[
  'aeris/maps/layers/advisories',
  'aeris/maps/layers/chlorophyll',
  'aeris/maps/layers/dewpoints',
  'aeris/maps/layers/heatindex',
  'aeris/maps/layers/humidity',
  'aeris/maps/layers/lightningstrikedensity',
  'aeris/maps/layers/precip',
  'aeris/maps/layers/qpf',
  'aeris/maps/layers/radar',
  'aeris/maps/layers/satellite',
  'aeris/maps/layers/satelliteglobal',
  'aeris/maps/layers/satellitevisible',
  'aeris/maps/layers/seasurfacetemps',
  'aeris/maps/layers/snowdepth',
  'aeris/maps/layers/snowfallaccum',
  'aeris/maps/layers/temps',
  'aeris/maps/layers/windchill',
  'aeris/maps/layers/winds',
  'aeris/maps/layers/osm'
], function() {});

define('mapbox',['leaflet'], function(L) {
  return L.mapbox;
});


define('aeris/maps/strategy/layers/mapbox',[
  'aeris/util',
  'aeris/maps/abstractstrategy',
  'leaflet',
  'mapbox'
], function(_, AbstractStrategy, Leaflet) {
  /**
   * @class aeris.maps.leaflet.layers.MapBox
   * @extends aeris.maps.AbstractStrategy
   *
   * @constructor
   */
  var MapBox = function(object, opt_options) {
    this.validateMapBoxDependencyExists_();

    AbstractStrategy.call(this, object, opt_options);
  };
  _.inherits(MapBox, AbstractStrategy);


  /**
   * @method createView_
   * @private
   */
  MapBox.prototype.createView_ = function() {
    var mapBoxId = this.object_.get('mapBoxId');
    return new Leaflet.mapbox.TileLayer(mapBoxId);
  };


  /**
   * Usually, we would let RequireJS make sure that
   * all of our dependencies are defined. In this case, though,
   * we want to make MapBox.js an optional dependency. So rather than
   * check that it exists when the Aeris.js script loads, we're going to
   * check that it exists only when a MapBox layer is created.
   *
   * @method validateMapBoxDependencyExists_
   */
  MapBox.prototype.validateMapBoxDependencyExists_ = function() {
    if (!Leaflet.mapbox) {
      throw new Error('Aeris.js requires MapBox.js in order to use aeris.maps.layers.MapBox layers. ' +
        'See https://www.mapbox.com/mapbox.js.');
    }
  };


  /**
   * @method setMap
   */
  MapBox.prototype.setMap = function(map) {
    AbstractStrategy.prototype.setMap.call(this, map);

    this.view_.addTo(map.getView());
  };


  /**
   * @method beforeRemove_
   * @private
   */
  MapBox.prototype.beforeRemove_ = function() {
    this.mapView_.removeLayer(this.view_);
  };


  return MapBox;
});

define('aeris/maps/layers/mapbox',[
  'aeris/util',
  'aeris/maps/layers/layer',
  'aeris/maps/strategy/layers/mapbox'
], function(_, Layer, MapBoxStrategy) {
  /**
   * @class aeris.maps.layers.MapBox
   * @extends aeris.maps.layers.Layer
   *
   * @constructor
   */
  var MapBox = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      strategy: MapBoxStrategy
    });

    Layer.call(this, opt_attrs, options);
  };
  _.inherits(MapBox, Layer);


  return _.expose(MapBox, 'aeris.maps.layers.MapBox');
});

define('aeris/packages/leaflet',[
  'aeris/maps/map',
  'aeris/packages/animations',
  'aeris/packages/markers',
  'aeris/packages/layers/tilelayers',
  'aeris/maps/layers/mapbox',

  'aeris/maps/strategy/map',
  'aeris/maps/strategy/layers/aeristile',
  'aeris/maps/strategy/layers/osm',
  'aeris/maps/strategy/layers/mapbox',
  'aeris/maps/strategy/markers/marker',
  'aeris/maps/strategy/markers/markercluster'
], function() {});

define('aeris/api/models/advisory',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.Advisory
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var Advisory = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'advisories'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Advisory, AerisApiModel);


  return _.expose(Advisory, 'aeris.api.models.Advisory');
});

define('aeris/api/collections/advisories',[
  'aeris/util',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/advisory'
], function(_, AerisApiClientCollection, Advisory) {
  /**
   * @publicApi
   * @class aeris.api.collections.Advisories
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
  */
  var Advisories = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'advisories',
      action: 'closest',
      model: Advisory,
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '1000mi'
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Advisories, AerisApiClientCollection);


  return _.expose(Advisories, 'aeris.api.collections.Advisories');
});

define('aeris/api/models/normal',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.Normal
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var Normal = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'normals',
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Normal, AerisApiModel);

  return _.expose(Normal, 'aeris.api.models.Normal');
});

define('aeris/api/collections/normals',[
  'aeris/util',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/normal'
], function(_, AerisApiClientCollection, Normal) {
  /**
   * @publicApi
   * @class aeris.api.collections.Normals
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
  */
  var Normals = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'normals',
      action: 'closest',
      model: Normal,
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '100mi'
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Normals, AerisApiClientCollection);


  return _.expose(Normals, 'aeris.api.collections.Normals');
});

define('aeris/api/models/observation',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.Observation
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var Observation = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'observations'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Observation, AerisApiModel);

  return _.expose(Observation, 'aeris.api.models.Observation');
});

define('aeris/api/collections/observations',[
  'aeris/util',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/observation'
], function(_, AerisApiClientCollection, Observation) {
  /**
   * @publicApi
   * @class aeris.api.collections.Observations
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
  */
  var Observations = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'observations',
      action: 'closest',
      model: Observation,
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '1000mi'
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Observations, AerisApiClientCollection);


  return _.expose(Observations, 'aeris.api.collections.Observations');
});

define('aeris/api/models/place',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.Place
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var Place = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'places'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Place, AerisApiModel);

  return _.expose(Place, 'aeris.api.models.Place');
});

define('aeris/api/collections/places',[
  'aeris/util',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/place'
], function(_, AerisApiClientCollection, Place) {
  /**
   * @publicApi
   * @class aeris.api.collections.Places
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
  */
  var Places = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'places',
      action: 'closest',
      model: Place,
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Places, AerisApiClientCollection);


  return _.expose(Places, 'aeris.api.collections.Places');
});

define('aeris/api/models/record',[
  'aeris/util',
  'aeris/api/models/aerisapimodel',
  'aeris/datehelper'
], function(_, AerisApiModel, DateHelper) {
  /**
   * @publicApi
   * @class aeris.api.models.Record
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var Record = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'records',
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '100mi',
      from: new DateHelper().addDays(-60).getDate()
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Record, AerisApiModel);

  return _.expose(Record, 'aeris.api.models.Record');
});

define('aeris/api/collections/records',[
  'aeris/util',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/record',
  'aeris/datehelper'
], function(_, AerisApiClientCollection, Record, DateHelper) {
  /**
   * @publicApi
   * @class aeris.api.collections.Records
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
   */
  var Records = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'records',
      action: 'closest',
      model: Record,
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '100mi',
      from: new DateHelper().addDays(-60).getDate()
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Records, AerisApiClientCollection);


  return _.expose(Records, 'aeris.api.collections.Records');
});

define('aeris/api/models/stormcell',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.StormCell
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var StormCell = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'stormcells',
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '100mi'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(StormCell, AerisApiModel);

  return _.expose(StormCell, 'aeris.api.models.StormCell');
});

define('aeris/api/collections/stormcells',[
  'aeris/util',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/stormcell'
], function(_, AerisApiClientCollection, StormCell) {
  /**
   * @publicApi
   * @class aeris.api.collections.StormCells
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
  */
  var StormCells = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'stormcells',
      action: 'closest',
      model: StormCell,
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '3000mi'
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(StormCells, AerisApiClientCollection);


  return _.expose(StormCells, 'aeris.api.collections.StormCells');
});

define('aeris/api/models/tide',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.Tide
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var Tide = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'tides',
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '100mi'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Tide, AerisApiModel);

  return _.expose(Tide, 'aeris.api.models.Tide');
});

define('aeris/api/collections/tides',[
  'aeris/util',
  'aeris/api/collections/aerisapiclientcollection',
  'aeris/api/models/tide'
], function(_, AerisApiClientCollection, Tide) {
  /**
   * @publicApi
   * @class aeris.api.collections.Tides
   * @extends aeris.api.collections.AerisApiClientCollection
   *
   * @constructor
  */
  var Tides = function(opt_models, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'tides',
      action: 'closest',
      model: Tide,
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto',
      limit: 100,
      radius: '100mi'
    });

    AerisApiClientCollection.call(this, opt_models, options);
  };
  _.inherits(Tides, AerisApiClientCollection);


  return _.expose(Tides, 'aeris.api.collections.Tides');
});

define('aeris/api/models/aerisbatchmodel',[
  'aeris/util',
  'aeris/api/models/aerisapimodel',
  'aeris/errors/apiresponseerror'
], function(_, AerisApiModel, ApiResponseError) {
  /**
   * Represents data from multiple Aeris API endpoints
   * combined into a single model.
   *
   * Note that AerisBatchModel does not currently support
   * per-model actions or queries.
   *
   * @class aeris.api.models.AerisBatchModel
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   *
   * @param {Object=} opt_attrs Set models as attribute values to enable batch requests.
   * @param {Object=} opt_options
   *
   * @param {aeris.api.params.Params} opt_options.params
   */
  var AerisBatchModel = function(opt_attrs, opt_options) {
    /**
     * A list of nested models, in the order of the last
     * API requests.
     *
     * @property modelsInOrder_
     * @type {Array.<aeris.api.models.AerisApiModel>}
     * @private
     */
    this.modelsInOrder_ = [];

    AerisApiModel.call(this, opt_attrs, opt_options);
  };
  _.inherits(AerisBatchModel, AerisApiModel);


  /**
   * @method getEndpointUrl_
   * @protected
   * @return {string}
   */
  AerisBatchModel.prototype.getEndpointUrl_ = function() {
    return _.compact([
      this.server_,
      'batch',
      this.id
    ]).join('/');
  };


  /**
   * @method serializeParams_
   * @protected
   * @param {aeris.api.params.models.Params} params
   * @return {Object}
   */
  AerisBatchModel.prototype.serializeParams_ = function(params) {
    // Save models in order,
    // so that we can parse the response based
    // on the order of the `responses` array
    // (because javascript does not necessarily maintain order in objects)
    this.modelsInOrder_ = this.getNestedModels_();

    return _.extend(params.toJSON(), {
      requests: this.getEncodedEndpoints_(this.modelsInOrder_)
    }, this.getApiKeyParams_());
  };


  /**
   * Return component models, which
   * are attributes of the batch model.
   *
   * @method getNestedModels_
   * @private
   * @return {Array.<aeris.api.models.AerisApiModel>}
   */
  AerisBatchModel.prototype.getNestedModels_ = function() {
    return this.values().filter(this.isModel_.bind(this));
  };


  /**
   * @method isModel_
   * @private
   * @param {Object} obj
   * @return {Boolean}
   */
  AerisBatchModel.prototype.isModel_ = function(obj) {
    return obj instanceof AerisApiModel;
  };


  /**
   * @method getEncodedEndpoints_
   * @private
   * @param {Array.<aeris.api.models.AerisApiModel>} apiModels
   * @return {string}
   */
  AerisBatchModel.prototype.getEncodedEndpoints_ = function(apiModels) {
    var requests = apiModels.map(function(model) {
      var endpoint = '/' + model.getEndpoint();

      return [
        endpoint,
        this.encodeModelParams_(model)
      ].join(encodeURIComponent('?'));
    }, this);

    return requests.join(',');
  };


  /**
   * @method encodeModelParams_
   * @private
   * @param {aeris.api.models.AerisApiModel} model
   * @return {string} Encoded model params.
   */
  AerisBatchModel.prototype.encodeModelParams_ = function(model) {
    var paramsStr;
    var params = model.getParams().toJSON();

    this.removeApiKeysFromParams_(params);

    paramsStr = _.map(params, function(val, key) {
      return key + '=' + val;
    }).join('&');

    return this.encodeParamsString_(paramsStr);
  };


  /**
   * @method encodeParamsString_
   * @private
   */
  AerisBatchModel.prototype.encodeParamsString_ = function(string) {
    // Aeris API only needs ? and & encoded.
    return string.
      replace('?', '%3F').
      replace('&', '%26');
  };


  /**
   * It is likely that each model contains idential
   * client_id/client_secret params. This will result in the
   * params being serialized into the query string for every model.
   *
   * For batch queries with many model, this could potentially
   * exceed the url limit.
   *
   * @method removeApiKeysFromParams_
   * @private
   * @param {Object} serializedParams
   */
  AerisBatchModel.prototype.removeApiKeysFromParams_ = function(serializedParams) {
    delete serializedParams.client_id;
    delete serializedParams.client_secret;
  };


  /**
   * Find the Aeris client_id and client_secret params
   * by searching through component models.
   *
   * @method getApiKeyParams_
   * @private
   * @return {Object}
   */
  AerisBatchModel.prototype.getApiKeyParams_ = function() {
    var apiKeyParams = {};

    this.modelsInOrder_.some(function(model) {
      apiKeyParams = model.getParams().pick('client_id', 'client_secret');

      // Stop looping once we've found the params.
      return apiKeyParams.client_id && apiKeyParams.client_secret;
    }, this);

    return apiKeyParams;
  };


  /**
   * @method isSuccessResponse_
   * @param {Object} res
   * @protected
   * @return {Boolean}
   */
  AerisBatchModel.prototype.isSuccessResponse_ = function(res) {
    var isBatchSuccess = !!res && res.success;
    if (!isBatchSuccess) {
      return false;
    }

    return res.response.responses.every(function(r) {
      return !!r && r.success;
    });
  };


  /**
   * @method createErrorFromResponse_
   * @protected
   * @param {Object} res
   * @return {Error}
   */
  AerisBatchModel.prototype.createErrorFromResponse_ = function(res) {
    var isTopLevelError = !!res.error;

    if (isTopLevelError) {
      return AerisApiModel.prototype.createErrorFromResponse_.call(this, res);
    }

    return res.response.responses.reduce(function(lastError, response) {
      var error;

      if (lastError || !response.error) {
        return lastError;
      }

      error = AerisApiModel.prototype.createErrorFromResponse_.call(this, response);

      // Temporary fix for Aeris API bug:
      // -- incorrect code for 'invalid_location' error when
      //    using batch requests.
      if (response.error.description === 'The requested location was not found.') {
        error.code = 'invalid_location';
      }

      return error;
    }, void 0);
  };


  /**
   * Sets batch response data onto nested models
   *
   * @override
   * @method parse
   * @param {Object} raw Raw response data.
   * @return {Object}
   */
  AerisBatchModel.prototype.parse = function(raw) {
    try {
      var responses = raw.response.responses;

      this.modelsInOrder_.forEach(function(model, index) {
        this.updateModelWithResponseData_(model, responses[index]);
      }, this);
    }
    catch (e) {
      throw new ApiResponseError('Unable to parse batch response data: ' +
        e.message);
    }

    return this.attributes;
  };


  /**
   * @method updateModelWithResponseData_
   * @private
   * @param {aeris.api.models.AerisApiModel} model
   * @param {Object} data Response data
   */
  AerisBatchModel.prototype.updateModelWithResponseData_ = function(model, data) {
    var modelAttrs = model.parse(data);
    model.set(modelAttrs);
  };


  /**
   * @method toJSON
   * @return {Object}
   */
  AerisBatchModel.prototype.toJSON = function() {
    var json = AerisApiModel.prototype.toJSON.call(this);

    // toJSON'ify nested models
    _.each(json, function(val, key) {
      if (val instanceof AerisApiModel) {
        json[key] = val.toJSON();
      }
    });

    return json;
  };


  /**
   * Clear data from each model.
   *
   * @override
   */
  AerisBatchModel.prototype.clear = function() {
    this.keys().forEach(function(attr) {
      var value = this.get(attr);

      // Clear our all nested models
      if (this.isModel_(value)) {
        value.clear();
      }

      // Remove regular attributes
      else {
        this.unset(attr);
      }
    }, this);
  };


  return AerisBatchModel;
});

define('aeris/api/models/forecast',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.Forecast
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
  */
  var Forecast = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'forecasts'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Forecast, AerisApiModel);


  return _.expose(Forecast, 'aeris.api.models.Forecast');
});

define('aeris/api/models/sunmoon',[
  'aeris/util',
  'aeris/api/models/aerisapimodel'
], function(_, AerisApiModel) {
  /**
   * @publicApi
   * @class aeris.api.models.Sunmoon
   * @extends aeris.api.models.AerisApiModel
   *
   * @constructor
   * @override
   */
  var Sunmoon = function(opt_attrs, opt_options) {
    var options = _.defaults(opt_options || {}, {
      endpoint: 'sunmoon',
      params: {}
    });

    _.defaults(options.params, {
      p: ':auto'
    });

    AerisApiModel.call(this, opt_attrs, options);
  };
  _.inherits(Sunmoon, AerisApiModel);

  return _.expose(Sunmoon, 'aeris.api.models.Sunmoon');
});

define('aeris/packages/api',[
  'aeris/api/collections/advisories',
  'aeris/api/collections/earthquakes',
  'aeris/api/collections/fires',
  'aeris/api/collections/geojsonfeaturecollection',
  'aeris/api/collections/lightning',
  'aeris/api/collections/normals',
  'aeris/api/collections/observations',
  'aeris/api/collections/places',
  'aeris/api/collections/records',
  'aeris/api/collections/stormcells',
  'aeris/api/collections/stormreports',
  'aeris/api/collections/tides',

  'aeris/api/models/advisory',
  'aeris/api/models/aerisbatchmodel',
  'aeris/api/models/earthquake',
  'aeris/api/models/fire',
  'aeris/api/models/forecast',
  'aeris/api/models/geojsonfeature',
  'aeris/api/models/lightning',
  'aeris/api/models/normal',
  'aeris/api/models/observation',
  'aeris/api/models/place',
  'aeris/api/models/record',
  'aeris/api/models/stormreport',
  'aeris/api/models/sunmoon',
  'aeris/api/models/tide'
], function() {});

define('aeris/geolocate/options/geolocateserviceoptions',['aeris/util'], function(_) {
  /**
   * Options for an {aeris.geolocate.AbstractGeolocateService}
   *
   * @class aeris.geolocate.options.GeolocateServiceOptions
   * @constructor
   *
   * @param {Object=} opt_options
   * @param {Boolean=} opt_options.enableHighAccuracy
   * @param {number} opt_options.timeout
   * @param {number} opt_options.maximumAge
   */
  var GeolocateServiceOptions = function(opt_options) {
    var options = _.defaults(opt_options || {}, this.getDefaultOptions());

    /**
     * @type {Boolean}
     * @property enableHighAccuracy
     */
    this.enableHighAccuracy = options.enableHighAccuracy;

    /**
     * @type {number}
     * @property maximumAge
     */
    this.maximumAge = options.maximumAge;

    /**
     * @type {number}
     * @property timeout
     */
    this.timeout = options.timeout;
  };


  /**
   * @return {Object}
   * @method getDefaultOptions
   */
  GeolocateServiceOptions.prototype.getDefaultOptions = function() {
    return _.clone(GeolocateServiceOptions.DEFAULT_OPTIONS);
  };


  /**
   * @static
   * @type {Object}
   */
  GeolocateServiceOptions.DEFAULT_OPTIONS = {
    enableHighAccuracy: false,
    maximumAge: 30000,
    timeout: 10000
  };


  return GeolocateServiceOptions;
});

define('aeris/geolocate/options/freegeoipserviceoptions',[
  'aeris/util',
  'aeris/geolocate/options/geolocateserviceoptions',
  'aeris/jsonp'
], function(_, GeolocateServiceOptions, JSONP) {
  /**
   * @class aeris.geolocate.options.FreeGeoIPServiceOptions
   * @extends aeris.geolocate.options.GeolocateServiceOptions
   *
   * @constructor
   * @override
   *
   * @param {string=} opt_options.ip_address
   * @param {Object=} opt_options.jsonp
  */
  var FreeGeoIPServiceOptions = function(opt_options) {
    var options = _.defaults(opt_options || {}, this.getDefaultOptions());

    this.jsonp = options.jsonp;
    this.ip_address = options.ip_address;

    GeolocateServiceOptions.call(this, options);
  };
  _.inherits(FreeGeoIPServiceOptions, GeolocateServiceOptions);



  /**
   * @method getDefaultOptions
   */
  FreeGeoIPServiceOptions.prototype.getDefaultOptions = function() {
    return _.clone(FreeGeoIPServiceOptions.DEFAULT_OPTIONS);
  };


  /**
   * @override
   */
  FreeGeoIPServiceOptions.DEFAULT_OPTIONS = _.extend({},
    GeolocateServiceOptions.DEFAULT_OPTIONS,
    {
      ip_address: '',
      jsonp: JSONP
    }
  );


  return FreeGeoIPServiceOptions;
});

define('aeris/geolocate/results/geolocateposition',['aeris/util'], function(_) {
  /**
   * Follows HTML5 Postion object specification,
   * except for the latLon property, which uses the aeris-standard format.
   *
   * Some position properties may be unavailable - if using
   * a IP location service, for example. In this case, their
   * values will be {null}.
   *
   * @param {Object} position Gelocated position.
   * @publicApi
   * @class aeris.geolocate.results.GeolocatePosition
   * @constructor
   */
  var GeolocatePosition = function(position) {
    _.defaults(position, {
      latLon: null,
      altitude: null,
      altitudeAccuracy: null,
      accuracy: null,
      heading: null,
      speed: null,
      timestamp: null
    });

    /**
     * @property {aeris.maps.LatLon} latLon
     */

    /**
     * @property {number|null} altitude
     */

    /**
     * @property {number|null} altitudeAccuracy
     */

    /**
     * @property {number|null} accuracy
     */

    /**
     * @property {number|null} heading
     */

    /**
     * @property {number|null} speed
     */

    /**
     * @property {Date|null} timestamp
     */

    return position;
  };

  return GeolocatePosition;
});

define('aeris/geolocate/errors/geolocateserviceerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.geolocate.errors.GeolocateServiceError
   * @extends aeris.errors.AbstractError
   *
   * @param {Object} errorObj
   * @constructor
   */
  var GeolocateServiceError = function(errorObj) {
    var ErrorType = new ErrorTypeFactory({
      name: 'GeolocateServiceError'
    });

    var error = new ErrorType(errorObj.message);


    /**
     * Error code.
     * @member {number} code
     */
    error.code = errorObj.code;

    /**
     * Permission denied error code
     * The user did not allow Geolocation.
     * @member {number} PERMISSION_DENIED
     */
    error.PERMISSION_DENIED = GeolocateServiceError.PERMISSION_DENIED;

    /**
     * Position unavailable error code
     * It is not possible to get the current location
     * @member {number} POSITION_UNAVAILABLE
     */
    error.POSITION_UNAVAILABLE = GeolocateServiceError.POSITION_UNAVAILABLE;

    /**
     * Timeout error code
     * The geolocation operation timed out.
     * @member {number} TIMEOUT
     */
    error.TIMEOUT = GeolocateServiceError.TIMEOUT;


    return error;
  };

  GeolocateServiceError.PERMISSION_DENIED = 1;
  GeolocateServiceError.POSITION_UNAVAILABLE = 2;
  GeolocateServiceError.TIMEOUT = 3;


  return GeolocateServiceError;
});

define('aeris/geolocate/freegeoipgeolocateservice',[
  'aeris/util',
  'aeris/promise',
  'aeris/geolocate/options/freegeoipserviceoptions',
  'aeris/geolocate/results/geolocateposition',
  'aeris/geolocate/errors/geolocateserviceerror'
], function(_, Promise, FreeGeoIPServiceOptions, GeolocatePosition, GeolocateServiceError) {
  /**
   * @publicApi
   * @class aeris.geolocate.FreeGeoIPGeolocateService
   * @implements aeris.geolocate.GeolocateServiceInterface
   * @constructor
   *
   * @param {aeris.geolocate.options.FreeGeoIPServiceOptions} opt_options
   */
  var FreeGeoIPGeolocateService = function(opt_options) {
    var options = new FreeGeoIPServiceOptions(opt_options);


    /**
     * The url of the FreeGeoIP API.
     * @type {string}
     * @private
     * @property url_
     */
    this.url_ = '//freegeoip.net/json/' + options.ip_address;

    /**
     * @type {aeris.JSONP}
     * @private
     * @property jsonp_
     */
    this.jsonp_ = options.jsonp;

    /**
     * The interval timer id
     * returned by window.setInterval.
     *
     * Saved, so we can clear it later.
     *
     * @type {number}
     * @private
     * @property watchId_
     */
    this.watchId_ = null;

    /**
     * The most recent results returned from the API.
     * @type {aeris.geolocate.results.GeolocatePosition}
     * @property lastPosition_
     */
    this.lastPosition_;
  };


  /**
   * @method getCurrentPosition
   */
  FreeGeoIPGeolocateService.prototype.getCurrentPosition = function() {
    var promise = new Promise();

    this.jsonp_.get(
      this.url_,
      {},
      _.bind(this.resolve_, this, promise)
    );

    return promise;
  };


  /**
   * @method watchPosition
   */
  FreeGeoIPGeolocateService.prototype.watchPosition = function(onSuccess, onError, opt_options) {
    var options = _.defaults(opt_options || {}, {
      interval: 3000
    });
    var noop = function() {
    };

    onSuccess || (onSuccess = noop);
    onError || (onError = noop);

    var updatePosition = (function() {
      this.getCurrentPosition().
        done(function(res) {
          var isNewPosition = !this.lastPosition_ || !_.isEqual(res, this.lastPosition_);

          // Only call callback if the
          // position has changed.
          if (isNewPosition) {
            this.lastPosition_ = res;
            onSuccess(res);
          }
        }).
        fail(onError);
    }.bind(this));

    this.watchId_ = window.setInterval(updatePosition, options.interval);
    updatePosition();
  };


  /**
   * @method clearWatch
   */
  FreeGeoIPGeolocateService.prototype.clearWatch = function() {
    if (_.isNull(this.watchId_)) {
      return;
    }

    window.clearInterval(this.watchId_);
    this.lastPosition_ = null;
  };


  /**
   * @method isSupported
   */
  FreeGeoIPGeolocateService.isSupported = function() {
    return true;
  };


  /**
   * Resolve a geolocation service promise with data returned
   * from FreeGeoIP.
   *
   * @param {aeris.Promise} promise
   * @param {Object} data
   * @private
   * @method resolve_
   */
  FreeGeoIPGeolocateService.prototype.resolve_ = function(promise, data) {
    var isMissingLocationData = !data || !_.isNumber(data.latitude) || !_.isNumber(data.longitude);

    if (isMissingLocationData) {
      promise.reject(new GeolocateServiceError({
        code: GeolocateServiceError.POSITION_UNAVAILABLE,
        message: 'FreeGeoIP returned unexpected data.'
      }));
    }
    else {
      promise.resolve(new GeolocatePosition({
        latLon: [data.latitude, data.longitude]
      }));
    }
  };


  return _.expose(FreeGeoIPGeolocateService, 'aeris.geolocate.FreeGeoIPGeolocateService');
});

define('aeris/geolocate/options/html5serviceoptions',[
  'aeris/util',
  'aeris/geolocate/options/geolocateserviceoptions'
], function(_, GeolocateServiceOptions) {
  var root = this;

  /**
   * @class aeris.geolocate.options.HTML5ServiceOptions
   * @extends aeris.geolocate.options.GeolocateServiceOptions
   *
   * @constructor
   * @override
   *
   * @param {Object=} opt_options.navigator
  */
  var HTML5ServiceOptions = function(opt_options) {
    var options = _.defaults(opt_options || {}, this.getDefaultOptions());

    this.navigator = options.navigator;

    GeolocateServiceOptions.call(this, options);
  };
  _.inherits(HTML5ServiceOptions, GeolocateServiceOptions);


  /**
   * @method getDefaultOptions
   */
  HTML5ServiceOptions.prototype.getDefaultOptions = function() {
    return _.clone(HTML5ServiceOptions.DEFAULT_OPTIONS);
  };


  /**
   * @override
   */
  HTML5ServiceOptions.DEFAULT_OPTIONS = _.extend({},
    GeolocateServiceOptions.DEFAULT_OPTIONS,
    {
      navigator: root.navigator
    }
  );


  return HTML5ServiceOptions;
});

define('aeris/geolocate/html5geolocateservice',[
  'aeris/util',
  'aeris/promise',
  'aeris/geolocate/options/html5serviceoptions',
  'aeris/geolocate/results/geolocateposition',
  'aeris/geolocate/errors/geolocateserviceerror'
], function(_, Promise, HTML5ServiceOptions, GeolocatePosition, GeolocateServiceError) {
  var root = this;

  /**
   * @publicApi
   * @class aeris.geolocate.HTML5GeolocateService
   * @implements aeris.geolocate.GeolocateServiceInterface
   *
   * @constructor
   * @override
   *
   * @param {aeris.geolocate.options.HTML5ServiceOptions=} opt_options
   */
  var HTML5GeolocateService = function(opt_options) {
    var options = new HTML5ServiceOptions(opt_options);

    /**
     * @type {Object}
     * @property navigatorOptions_
     */
    this.navigatorOptions_ = _.pick(options,
      'enableHighAccuracy',
      'maximumAge',
      'timeout'
    );


    /**
     * @type {Navigator} HTML5 Navigator object
     * @private
     * @property navigator_
     */
    this.navigator_ = options.navigator;


    /**
     * If the user's position is being watched,
     * this is the ID of the returned by Navigator.geolocation.watchPosition.
     *
     * Is required to clear the watch.
     *
     * @type {number}
     * @property watchId_
     */
    this.watchId_ = null;
  };


  /**
   * @method getCurrentPosition
   */
  HTML5GeolocateService.prototype.getCurrentPosition = function() {
    var promise = new Promise();
    var callback = _.bind(function(res) {
      promise.resolve(this.createPosition_(res));
    }, this);
    var errback = _.bind(function(err) {
      promise.reject(this.createGeolocateError_(err));
    }, this);


    if (!HTML5GeolocateService.isSupported(this.navigator_)) {
      promise.reject(this.createServiceUnavailableError_());
    }
    else {
      this.navigator_.geolocation.getCurrentPosition(callback, errback, this.navigatorOptions_);
    }

    return promise;
  };


  /**
   * @method watchPosition
   */
  HTML5GeolocateService.prototype.watchPosition = function(onSuccess, onError, opt_options) {
    var self = this;

    if (!HTML5GeolocateService.isSupported(this.navigator_)) {
      onError(this.createServiceUnavailableError_());
    }
    else {
      this.watchId_ = this.navigator_.geolocation.watchPosition(
        function(res) {
          onSuccess(self.createPosition_(res));
        },
        function(error) {
          onError(self.createGeolocateError_(error));
        },
        this.navigatorOptions_
      );
    }
  };


  /**
   * @private
   * @return {aeris.geolocate.errors.GeolocateServiceError}
   * @method createServiceUnavailableError_
   */
  HTML5GeolocateService.prototype.createServiceUnavailableError_ = function() {
    return this.createGeolocateError_({
      message: 'HTML5 Geolocation is not available.',
      code: GeolocateServiceError.POSITION_UNAVAILABLE
    });
  };


  /**
   * @method clearWatch
   */
  HTML5GeolocateService.prototype.clearWatch = function() {
    if (_.isNull(this.watchId_)) { return; }

    this.navigator_.geolocation.clearWatch(this.watchId_);
    this.watchId_ = null;
  };


  /**
   * @method isSupported
   * @param {Navigator=} opt_navigator The navigator object
   *        for which to check geolocation support. Defaults
   *        to the global window.navigator object.
   */
  HTML5GeolocateService.isSupported = function(opt_navigator) {
    var navigator = arguments.length ? opt_navigator : root.navigator;

    return !!(navigator && navigator.geolocation);
  };


  /**
   * @param {Object} position Position data from the Geolocation API.
   * @return {aeris.geolocate.results.GeolocatePosition}
   * @private
   * @method createPosition_
   */
  HTML5GeolocateService.prototype.createPosition_ = function(position) {
    return new GeolocatePosition(_.extend(
      {
        latLon: [position.coords.latitude, position.coords.longitude],
        timestamp: position.timestamp || null
      },
      _.pick(position.coords,
        'altitude',
        'altitudeAccuracy',
        'accuracy',
        'heading',
        'speed'
      )
    ));
  };

  /**
   * @param {Object} error Error data from the Geolocation API.
   * @return {aeris.geolocate.errors.GeolocateServiceError}
   * @private
   * @method createGeolocateError_
   */
  HTML5GeolocateService.prototype.createGeolocateError_ = function(error) {
    return new GeolocateServiceError(error);
  };


  /**
   * @method setNavigator
   * @param {Navigator} navigator Set the navigator object to use for geolocation.
   */
  HTML5GeolocateService.prototype.setNavigator = function(navigator) {
    this.navigator_ = navigator;
  };



  return _.expose(HTML5GeolocateService, 'aeris.geolocate.HTML5GeolocateService');
});

define('aeris/geocode/config',[
  'aeris/util',
  'aeris/model',
  'module'
], function(_, Model, module) {
  return new Model(module.config());
});

define('aeris/errors/invalidconfigerror',[
  'aeris/errors/errortypefactory'
], function(ErrorTypeFactory) {
  /**
   * @class aeris.errors.InvalidConfigError
   * @extends aeris.errors.AbstractError
  */
  return new ErrorTypeFactory({
    name: 'InvalidConfigError'
  });
});

define('aeris/geocode/geocodeserviceresponse',[],function() {
  /**
   * A response to a geocode service request
   *
   * @class aeris.geocode.GeocodeServiceResponse
   * @constructor
   */
  var GeocodeServiceResponse = function(responseObj) {
    /**
     * @type {aeris.LatLon|undefined} latLon Geocoded lat/Lon coordinates.
     */

    /**
     * @type {Object} status
     * @property {aeris.geocode.GeocodeServiceStatus} code Aeris error code.
     * @property {*|undefined} apiCode Code returned by the Geocoding service API.
     * @property {string} message Status message.
     */


    return responseObj;
  };

  return GeocodeServiceResponse;
});

define('aeris/geocode/geocodeservicestatus',['aeris/util'], function(_) {
  /**
   * Possible status values provided by a {aeris.geocode.GeocodeServiceResponse}.
   *
   * @class aeris.geocode.GeocodeServiceStatus
   * @static
   * @readonly
   * @enum {string}
   */
  return _.expose({
    OK: 'OK',
    /**
     * The API return an unspecified error.
     * See the response message for error details.
     */
    API_ERROR: 'API_ERROR',
    /**
     * Unable to process the geocoding request.
     * See the response message for error details.
     */
    INVALID_REQUEST: 'INVALID_REQUEST',
    NO_RESULTS: 'NO_RESULTS'
  }, 'aeris.geocode.GeocodeServiceStatus');
});

define('aeris/geocode/mapquestgeocodeservice',[
  'aeris/util',
  'aeris/promise',
  'aeris/jsonp',
  'aeris/geocode/config',
  'aeris/errors/invalidconfigerror',
  'aeris/geocode/geocodeserviceresponse',
  'aeris/geocode/geocodeservicestatus'
], function(_, Promise, JSONP, geocodeConfig, InvalidConfigError, GeocodeServiceResponse, GeocodeServiceStatus) {
  /**
   * MapQuest Geocoding Service
   * See http://open.mapquestapi.com/geocoding
   *
   * @publicApi
   * @class aeris.geocode.MapQuestGeocodeService
   * @implements aeris.geocode.GeocodeServiceInterface
   *
   * @constructor
   * @param {Object=} opt_options
   * @param {string} opt_options.apiId
   *                 Mapquest apiId can alternatively be configured in 'aeris/geocode/config'
   *                 via RequireJS `config` option.
   */
  var MapQuestGeocodeService = function(opt_options) {
    var options = _.defaults(opt_options || {}, {
      apiId: geocodeConfig.get('apiId')
    });


    /**
     * The MapQuest API id.
     *
     * @property apiId_
     * @type {*|options.apiId}
     * @private
     */
    this.apiId_ = options.apiId;

    /**
     * Base URL for MapQuest Geocoding service.
     * @type {string}
     * @private
     * @property serviceUrl_
     */
    this.serviceUrl_ = '//open.mapquestapi.com/geocoding/v1/address';


    /**
     * JSONP service.
     *
     * @type {Object} See {aeris.JSONP} for expected behavior.
     * @property {Function} get
     * @protected
     * @property jsonp_
     */
    this.jsonp_ = JSONP;
  };


  /**
   * @throws {aeris.errors.InvalidConfigError} If no Mapquest apiId is provided
   * @override
   * @method geocode
   */
  MapQuestGeocodeService.prototype.geocode = function(location) {
    var promise = new Promise();
    var uri = this.getMapQuestUri_();
    var query = { location: location };

    this.jsonp_.get(uri, query, _.bind(function(res) {
      if (!res || !res.info || _.isUndefined(res.info.statuscode)) {
        promise.reject(this.createUnexpectedResultsError_(res));
      }
      else if (this.isStatusCodeErrorResponse_(res)) {
        promise.reject(this.createStatusCodeError_(res));
      }
      else if (this.isNoResultsErrorResponse_(res)) {
        promise.reject(this.createNoResultsError_(res));
      }
      else {
        promise.resolve(this.createGeocodeResponse_(res));
      }
    }, this));

    return promise;
  };


  /**
   * @private
   * @return {string}
   * @method getMapQuestUri_
   */
  MapQuestGeocodeService.prototype.getMapQuestUri_ = function() {
    this.ensureApiId_();
    return this.serviceUrl_ + '?key=' + this.apiId_;
  };


  /**
   * @throws {aeris.errors.InvalidConfigError}
   * @private
   * @method ensureApiId_
   */
  MapQuestGeocodeService.prototype.ensureApiId_ = function() {
    this.apiId_ = this.apiId_ || geocodeConfig.get('apiId');

    if (!this.apiId_) {
      throw new InvalidConfigError('The MapQuestGeocodeService requires an apiId');
    }
  };


  /**
   * @private
   * @param {Object} res
   * @return {Boolean}
   * @method isStatusCodeErrorResponse_
   */
  MapQuestGeocodeService.prototype.isStatusCodeErrorResponse_ = function(res) {
    var isResDefined = res && res.info;
    var statusCode = isResDefined ? parseInt(res.info.statuscode) : null;

    return !_.isNumeric(statusCode) || statusCode >= 400;
  };


  /**
   * @private
   * @param {Object} res
   * @return {Boolean}
   * @method isNoResultsErrorResponse_
   */
  MapQuestGeocodeService.prototype.isNoResultsErrorResponse_ = function(res) {
    var isResDefined = res && res.results;
    return isResDefined &&
      (!res.results.length || !res.results[0].locations || !res.results[0].locations.length);
  };


  /**
   * @private
   * @param {Object} res
   * @return {aeris.geocode.GeocodeServiceResponse}
   * @method createUnexpectedResultsError_
   */
  MapQuestGeocodeService.prototype.createUnexpectedResultsError_ = function(res) {
    return new GeocodeServiceResponse({
      latLon: [],
      status: {
        code: GeocodeServiceStatus.API_ERROR,
        apiCode: '',
        message: 'The MapQuest Geolocation API returned an unexpected response.'
      }
    });
  };


  /**
   * @private
   * @param {Object} res
   * @return {aeris.geocode.GeocodeServiceResponse}
   * @method createStatusCodeError_
   */
  MapQuestGeocodeService.prototype.createStatusCodeError_ = function(res) {
    var statusMap = {
      0: GeocodeServiceStatus.OK,
      400: GeocodeServiceStatus.INVALID_REQUEST,
      403: GeocodeServiceStatus.API_ERROR,
      500: GeocodeServiceStatus.API_ERROR
    };

    return new GeocodeServiceResponse({
      latLon: [],
      status: {
        code: statusMap[res.info.statuscode] || GeocodeServiceStatus.API_ERROR,
        apiCode: res.info.statuscode,
        message: res.info.messages.join('; ')
      }
    });
  };


  /**
   * @private
   * @param {Object} res
   * @return {aeris.geocode.GeocodeServiceResponse}
   * @method createNoResultsError_
   */
  MapQuestGeocodeService.prototype.createNoResultsError_ = function(res) {
    return new GeocodeServiceResponse({
      latLon: [],
      status: {
        code: GeocodeServiceStatus.NO_RESULTS,
        apiCode: res.info.statuscode,
        message: res.info.messages.join('; ')
      }
    });
  };


  /**
   * @private
   * @param {Object} res
   * @return {aeris.geocode.GeocodeServiceResponse}
   * @method createGeocodeResponse_
   */
  MapQuestGeocodeService.prototype.createGeocodeResponse_ = function(res) {
    var geocodedLocation = res.results[0].locations[0];

    return new GeocodeServiceResponse({
      latLon: [
        parseFloat(geocodedLocation.latLng.lat),
        parseFloat(geocodedLocation.latLng.lng)
      ],
      status: {
        code: GeocodeServiceStatus.OK,
        apiCode: res.info.statuscode,
        message: res.info.messages.join('; ')
      }
    });
  };


  /**
   * Set jsonp service
   *
   * @param {Object} jsonp
   * @method setJSONP
   */
  MapQuestGeocodeService.prototype.setJSONP = function(jsonp) {
    this.jsonp_ = jsonp;
  };


  return _.expose(MapQuestGeocodeService, 'aeris.geocode.MapQuestGeocodeService');
});

  // In order for modules to be immediately available
  // under the aeris namespace, packages must be required here.
  
    require('aeris/packages/leaflet');
  
    require('aeris/packages/api');
  
    require('aeris/geolocate/freegeoipgeolocateservice');
  
    require('aeris/geolocate/html5geolocateservice');
  
    require('aeris/geocode/mapquestgeocodeservice');
  
}));