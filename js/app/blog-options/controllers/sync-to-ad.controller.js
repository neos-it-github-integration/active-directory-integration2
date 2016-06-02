(function () {
    app.controller('SyncToAdController', SyncToAdController);

    SyncToAdController.$inject = ['$scope', '$http', 'DataService', 'alertify'];

    function SyncToAdController($scope, $http, DataService, alertify) {
        var vm = this;

        $scope.permissionOptions = DataService.getPermissionOptions();

        $scope.$on('options', function (event, data) {
            $scope.option = {
                sync_to_ad: $valueHelper.findValue("sync_to_ad", data),
                sync_to_ad_use_global_user: $valueHelper.findValue("sync_to_ad_use_global_user", data),
                sync_to_ad_global_user: $valueHelper.findValue("sync_to_ad_global_user", data),
                sync_to_ad_global_password: $valueHelper.findValue("sync_to_ad_global_password", data),
                sync_to_ad_authcode: $valueHelper.findValue("sync_to_ad_authcode", data)
            };

            $scope.permission = {
                sync_to_ad: $valueHelper.findPermission("sync_to_ad", data),
                sync_to_ad_use_global_user: $valueHelper.findPermission("sync_to_ad_use_global_user", data),
                sync_to_ad_global_user: $valueHelper.findPermission("sync_to_ad_global_user", data),
                sync_to_ad_global_password: $valueHelper.findPermission("sync_to_ad_global_password", data),
                sync_to_ad_authcode: $valueHelper.findPermission("sync_to_ad_authcode", data)
            };
        });

        $scope.$on('validation', function (event, data) {
            $scope.messages = {
                sync_to_ad: $valueHelper.findMessage("sync_to_ad", data),
                sync_to_ad_use_global_user: $valueHelper.findMessage("sync_to_ad_use_global_user", data),
                sync_to_ad_global_user: $valueHelper.findMessage("sync_to_ad_global_user", data),
                sync_to_ad_global_password: $valueHelper.findMessage("sync_to_ad_global_password", data),
                sync_to_ad_authcode: $valueHelper.findMessage("sync_to_ad_authcode", data)
            };
        });

        $scope.newAuthCode = function () {
             alertify.confirm("Do you really want to regenerate a new AuthCode?", function() {
                $http.post('admin-ajax.php', {
                    action: 'adi2_blog_options',
                    security: document.adi2.security,
                    subAction: 'generateNewAuthCode'
                }).then(function successCallback(response) {
                    $scope.option.sync_to_ad_authcode = response.data['newAuthCode'];
                }, function errorCallback(response) {
                    // called asynchronously if an error occurs
                    // or server returns response with an error status.
                });
            }, function() {
                
            });
        };

        $scope.getPreparedOptions = function () {
            return DataService.cleanOptions($scope.option);
        };

        $scope.containsErrors = function () {
            return (!$arrayUtil.containsOnlyNullValues($scope.messages));
        };
    }
})();