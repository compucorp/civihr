/**
 * moment() wrapper which assumes API formatting and applies caching
 *
 * We probably won't fill the cache, but... if we did have a lot of data...
 * then we would want some non-memory-overflowing behavior...
 *
 * @param ts
 * @return {*}
 */
CRM.HRAbsenceApp.moment = function(ts) {
  /*
  var CACHE_LIMIT = 400, CACHE_CHUNK = 20, me = CRM.HRAbsenceApp.moment;
  var c = me.cache = (me.cache || new me.Cache());
  if (!c.items[ts]) {
    if (c.size >= CACHE_LIMIT) {
      c.drop(CACHE_CHUNK);
    }
    c.put(ts, moment(ts, CRM.absenceApp.apiTsFmt));
  }
  return c.items[ts];
  */
  return moment(ts, CRM.absenceApp.apiTsFmt)
};

CRM.HRAbsenceApp.moment.Cache = function() {
  this.size = 0;
  this.items = {};
  this.drop = function(count) {
    var keys = _.keys(this.items);
    for (var i = 0; i < count; i++) {
      delete this.items[keys[i]];
      this.size--;
    }
  };
  this.put = function(key, value) {
    this.items[key] = value;
    this.size++;
  }
};