define([], function () {

  var prefix = {
    "is_error": 0,
    "version": 3,
    "count": 1,
    "id": 3
  };

  var commentsWithID = [{
    "comment_id": "3",
    "leave_request_id": "17",
    "text": "test comment message",
    "contact_id": "202",
    "created_at": "2017-02-14 13:48:33"
  }];

  var commentsWithNoID = [{
    "leave_request_id": "18",
    "text": "test comment message",
    "contact_id": "202",
    "created_at": "2017-02-14 13:48:33"
  }];

  var deleteComment = {
    count: 1,
    is_error: 0,
    values: 1,
    version: 3
  };

  return {
    getComments: function () {
      var returnValue = prefix;
      returnValue.values = commentsWithID;

      return returnValue;
    },
    getCommentsWithMixedIDs: function () {
      var returnValue = prefix;
      returnValue.values = commentsWithID.concat(commentsWithNoID);

      return returnValue;
    },
    deleteComment: function () {
      return deleteComment;
    }
  }
});
