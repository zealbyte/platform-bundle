
/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Serial queue for asynchronous operations
 *
 * @url http://techscursion.com/2014/12/serial-promise-queue.html
 */
Queue = function() {
  var queueEnd = Promise.resolve();

  this.enqueue = function(item) {
    var thunk;
    var promise = new Promise(function(resolve, reject) {
      thunk = function() {
        if (item.length > 0) {
          item(resolve, reject);
        } else {
          var result = item();
          if (result && result.then) {
            result.then(resolve).catch(reject);
          } else {
            resolve();
          }
        }
      };
    });

    queueEnd.then(thunk).catch(thunk);
    queueEnd = promise;

    return promise;
  };
};

function zGet (prop)
{
	if (prop.constructor !== String)
		throw new TypeError('zGet requires a string property map.');

	if (!window.zdata || window.zdata.constructor !== Object)
		window.zdata = {}

	return zGetProperty(prop, window.zdata);
}

function zSet (prop, value)
{
	if (prop.constructor !== String)
		throw new TypeError('zGet requires a string property map.');

	if (!window.zdata || window.zdata.constructor !== Object)
		window.zdata = {}

	window.zdata = zSetProperty(prop, value, window.zdata);
}

function zGetProperty (prop, data)
{
	if (data.hasOwnProperty(prop)) return data[prop];

	return null;
}

function zSetProperty (prop, value, data)
{
	data[prop] = value;

	return data;
}

function zRequire (packages, success_cb, error_cb)
{
	var cbReturn = false;

	if (!success_cb || 'function' === typeof success_cb) {
		zRequirePackages(packages).then(function (response) {
			cbReturn = success_cb ? success_cb(response) : true;
		}).catch(function (error) {
			if ('function' === typeof error_cb) {
				error_cb(error);
			}
		});
	}

	return cbReturn;
}

function zRequirePackages (packages)
{
	var packageNames = [];

	if (packages.constructor !== Array) packages = [packages];

	packages.forEach(function (package) {
		if (zPackageVerify(package)) {
			zPushPackage(package);
			packageNames.push(package.name);
		}
	});

	return zPackage(packageNames);
}

function zPushPackage (package)
{
	var has = false;
	var currPackages = zGet('packages');

	if (!currPackages) currPackages = [];

	package.name = package.name.toLowerCase();

	currPackages.forEach(function (currPackage) {
		if (currPackage.name == package.name) has = true;
	});

	if (!has) {
		currPackages.push(package);
		zSet('packages', currPackages);
	}
}

function zPackage (packageNames)
{
	var q = new Queue();

	if (packageNames.constructor === String) packageNames = [packageNames];

	return new Promise(function(pkgsResolve, pkgsReject) {
		Promise.all(packageNames.map(function (packageName) {
			return q.enqueue(function (pkResolve, pkReject) {
				zPackageRequire(pkResolve, pkReject, packageName);
			});
		})).then(function (response) {
			pkgsResolve(response);
		}).catch(function(error) {
			pkgsReject(error);
		});
	});
}

// May need polyfill from core-js
function zPackageRequire (pkResolve, pkReject, packageName)
{
	var q = new Queue();
	var allPackages = zGet('packages');
	var applied = zGet('applied');

	if (!applied) applied = [];

	if (!applied.includes(packageName)) {
		allPackages.forEach(function (package) {
			if (packageName == package.name) {
				zPackage(package.dependencies).then(function (response) {
					Promise.all([
						q.enqueue(function (resourceResolve, resourceReject) {
							requirePackageResources(resourceResolve, resourceReject, package.resources);
						}),
					]).then(function (response) {
						applied.push(packageName);
						zSet('applied', applied);

						pkResolve(packageName);
					}).catch(function (error) {
						pkReject(error);
					});
				});
			}
		});
	} else {
		pkResolve(packageName);
	}
}

function requirePackageResources (resourceResolve, resourceReject, resources)
{
	var q = new Queue();

	if (resources.constructor !== Array) resources = [resources];

	Promise.all(resources.map(function(resource) {
		if (!zResourceVerify(resource))
			throw new Error("Bad package resource");

		return q.enqueue(function (resolve, reject) {
			if ('application/javascript' == resource.type) {
				requirePackageJsAppend(resolve, reject, resource.path);
			} else if ('text/css' == resource.type) {
				requirePackageCssAppend(resolve, reject, resource.path);
			}
		});
	})).then(function (response) {
		resourceResolve(response);
	}).catch(function (error) {
		resourceReject(error);
	});
}

function requirePackageCssAppend (resolve, reject, url)
{
	var head = document.head || document.getElementsByTagName('head')[0];
	var link = document.createElement('link');

	link.onload = function () {
		resolve(link);
	};

	link.onerror = function (error) {
		reject(error);
	};

	link.type = 'text/css';
	link.rel = 'stylesheet';
	link.href = url;
	link.async = false;

	head.appendChild(link);
}

function requirePackageJsAppend (resolve, reject, url)
{
	var head = document.head || document.getElementsByTagName('head')[0];
	var script = document.createElement('script');

	script.onload = function () {
		resolve(script);
	};
	script.onerror = function (error) {
		reject(error);
	};

	script.type = 'text/javascript';
	script.src = url;
	script.async = false;

	head.appendChild(script);
}

function zPackageVerify (package)
{
	var props = ['name','version','dependencies','resources'];

	return props.every(function(prop) {return prop in package});
}

function zResourceVerify (resource)
{
	var props = ['name','type','path'];

	return props.every(function(prop) {return prop in resource});
}

