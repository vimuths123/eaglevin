var urlseg = 'http://localhost:8000/';
$(function () {
    $("#demo-input-local").tokenInput("http://localhost:8000/productCategories/jsonCat", {
        theme: "facebook",
        preventDuplicates: true
    });

    $('.add_category').validate({
        rules: {
            cat_title: "required"
        }
    });

    $('.add_category').submit(function (e) {
        e.preventDefault();
    });
});

angular.module("add_category_app", [], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).controller("addController", function ($scope, $timeout) {
    $scope.addForm = {};
    $scope.cat_success = false;


    $scope.clear = function () {
        //Select tag cannot set default without this
        $timeout(jQuery.uniform.update, 0);

        $scope.addForm.catTitle = "";
        $scope.addForm.catDesc = "";
        $("#demo-input-local").tokenInput("clear");


    };

    $scope.hide_success = function () {
        $scope.cat_success = false;
    };

    $scope.save = function () {
        if ($('.add_category').valid())
        {
            //Add the form to FormData object
            var formData = new FormData($(".add_category")[0]);

            //Select tag cannot set default without this
            $timeout(jQuery.uniform.update, 0);

            var fdata = JSON.stringify($scope.addForm, null, 4);

            //append angular js model values
            formData.append('object', fdata);

            formData.append('related', $('.inp').val());

//            $.post('http://localhost:8000/productCategories/json_add', {formData: fdata, related: $('.inp').val()}, function (data) {
//                alert(data);
//            });

            //send values with ajax
            $.ajax({
                type: 'POST',
                url: urlseg + 'productCategories/json_add',
                data: formData, // Adding FormData object like this is easier
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data === "true") {
                        $scope.cat_success = true;
                        $scope.$apply();
//                        alert(data);
                    }
//                    alert(data);
                }
            });

            //Set input fields to default
            $scope.addForm.catTitle = "";
            $scope.addForm.catDesc = "";
            $("#demo-input-local").tokenInput("clear");

            //Stop giving error messages after submitting
            $(".add_category").validate().cancelSubmit = true;
        }

    };
});


angular.module("update_category_app", [], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).controller("updateController", function ($scope, $timeout) {

//    Call tocken input with view initialized values
    $scope.relateds = [];
    $(function () {
        $("#update-input-local").tokenInput("http://localhost:8000/productCategories/jsonCat", {
            theme: "facebook",
            preventDuplicates: true,
            prePopulate: $scope.relateds
        });
    });

    $scope.addForm = {};
    $scope.cat_success = false;

    $scope.clear = function () {
        //Select tag cannot set default without this
        $timeout(jQuery.uniform.update, 0);

        $scope.addForm.catTitle = "";
        $scope.addForm.catDesc = "";
        $("#update-input-local").tokenInput("clear");
    };

    $scope.hide_success = function () {
        $scope.cat_success = false;
    };

    $scope.save = function () {
        if ($('.add_category').valid())
        {
            //Add the form to FormData object
            var formData = new FormData($(".add_category")[0]);

            //Select tag cannot set default without this
            $timeout(jQuery.uniform.update, 0);

            var fdata = JSON.stringify($scope.addForm, null, 4);

            //append angular js model values
            formData.append('object', fdata);

            formData.append('related', $('.inp').val());

//            $.post('http://localhost:8000/productCategories/json_add', {formData: fdata, related: $('.inp').val()}, function (data) {
//                alert(data);
//            });

            //send values with ajax
            $.ajax({
                type: 'POST',
                url: urlseg + 'productCategories/json_update',
                data: formData, // Adding FormData object like this is easier
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data === "true") {
                        $scope.cat_success = true;
                        $scope.$apply();
                    }
                }
            });

            //Set input fields to default
//            $scope.addForm.catTitle = "";
//            $scope.addForm.catDesc = "";
//            $("#update-input-local").tokenInput("clear");

            //Stop giving error messages after submitting
//            $(".add_category").validate().cancelSubmit = true;
        }

    };
});


angular.module("view_category_app", ["checklist-model"], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).controller("viewController", function ($scope, $http) {
    $scope.categories = {};
    $scope.allcategories = {};
    $scope.totalCategories = 0;
//    $scope.firstEntry = 0;
//    $scope.lastEntry = 0;

//<-----------Get categories to data table------------------>
    $http.post(urlseg + "productCategories/getAll").success(function (data) {
        $scope.allcategories = data;
        $scope.totalCategories = Object.keys($scope.allcategories).length;
        $scope.firstEntry = 1;
        $scope.lastEntry = perpage;
//        var per = perpage;
        $http.post(urlseg + 'productCategories/json_paginate', {startpoint: 0, perpage: perpage}).success(function (data) {
            $scope.categories = data;
        });

        var recs = Object.keys(data).length; //Get number of categories 

        if (recs <= perpage) {
            $('.pagination_parent').hide();
        } else {
            $('.pagination_parent').show();
        }

        var options = {
            currentPage: 1,
            totalPages: Math.ceil(recs / perpage),
            alignment: 'right',
            itemTexts: function (type, page, current) {
                switch (type) {
                    case "first":
                        return "First";
                    case "prev":
                        return "Previous";
                    case "next":
                        return "Next";
                    case "last":
                        return "Last";
                    case "page":
                        return page;
                }
            },
            onPageClicked: function (e, originalEvent, type, page) {
                var startpoint = (page * perpage) - perpage;
                $http.post(urlseg + 'productCategories/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function (data) {
                    $scope.categories = data;
                    $scope.firstEntry = startpoint + 1;
                    if ($scope.totalCategories < startpoint + perpage) {
                        $scope.lastEntry = $scope.totalCategories;
                    } else {
                        $scope.lastEntry = startpoint + perpage;
                    }
                });
            }
        };

        $('#pagination_div').bootstrapPaginator(options);
    });
//<-----------Get products to data table------------------>


//<-----------Get Filtered products by search------------------>
    $scope.search = function () {
        $http.post(urlseg + "productCategories/jason_searchAll", {searchedVal: $scope.searchText}).success(function (data) {
            $scope.allcategories = data;
            $scope.totalCategories = Object.keys($scope.allcategories).length;
            $scope.firstEntry = 1;
            $scope.lastEntry = perpage;

            $http.post(urlseg + 'productCategories/json_searchPaginate', {searchedVal: $scope.searchText, startpoint: 0, perpage: perpage}).success(function (data) {
                $scope.categories = data;
            });

            var recs = Object.keys(data).length; //Get number of products 

            if (recs <= perpage) {
                $('.pagination_parent').hide();
            } else {
                $('.pagination_parent').show();
            }

            var options = {
                currentPage: 1,
                totalPages: Math.ceil(recs / perpage),
                alignment: 'right',
                itemTexts: function (type, page, current) {
                    switch (type) {
                        case "first":
                            return "First";
                        case "prev":
                            return "Previous";
                        case "next":
                            return "Next";
                        case "last":
                            return "Last";
                        case "page":
                            return page;
                    }
                },
                onPageClicked: function (e, originalEvent, type, page) {
                    var startpoint = (page * perpage) - perpage;
                    $http.post(urlseg + 'productCategories/json_searchPaginate', {searchedVal: $scope.searchText, startpoint: startpoint, perpage: perpage}).success(function (data) {
                        $scope.categories = data;
                        $scope.firstEntry = startpoint + 1;
                        if ($scope.totalCategories < startpoint + perpage) {
                            $scope.lastEntry = $scope.totalCategories;
                        } else {
                            $scope.lastEntry = startpoint + perpage;
                        }
                    });
                }
            };

            $('#pagination_div').bootstrapPaginator(options);
        });
    };
//<-----------Get Filtered products by search------------------>

    $scope.chkedCategories = {
        ids: []
    };

    var ids = $scope.chkedCategories.ids;

    $scope.allDelete = function () {
        var deleteUser = window.confirm('Are you absolutely sure you want to delete?');

        if (deleteUser) {
            if (!ids.length) {
                alert("You haven't select any product to delete..");
            } else {
                $http.post(urlseg + "productCategories/multiDelete", {ids: ids}).success(function (data) {
                    $scope.allcategories = data;
                    $scope.totalCategories = Object.keys($scope.allcategories).length;
                    $scope.firstEntry = 1;
                    $scope.lastEntry = perpage;

                    $http.post(urlseg + 'productCategories/json_paginate', {startpoint: 0, perpage: perpage}).success(function (data) {
                        $scope.categories = data;
                    });

                    var recs = Object.keys(data).length; //Get number of products 

                    if (recs <= perpage) {
                        $('.pagination_parent').hide();
                    } else {
                        $('.pagination_parent').show();
                    }

                    var options = {
                        currentPage: 1,
                        totalPages: Math.ceil(recs / perpage),
                        alignment: 'right',
                        itemTexts: function (type, page, current) {
                            switch (type) {
                                case "first":
                                    return "First";
                                case "prev":
                                    return "Previous";
                                case "next":
                                    return "Next";
                                case "last":
                                    return "Last";
                                case "page":
                                    return page;
                            }
                        },
                        onPageClicked: function (e, originalEvent, type, page) {
                            var startpoint = (page * perpage) - perpage;
                            $http.post(urlseg + 'productCategories/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function (data) {
                                $scope.categories = data;
                                $scope.firstEntry = startpoint + 1;
                                if ($scope.totalCategories < startpoint + perpage) {
                                    $scope.lastEntry = $scope.totalCategories;
                                } else {
                                    $scope.lastEntry = startpoint + perpage;
                                }
                            });
                        }
                    };

                    $('#pagination_div').bootstrapPaginator(options);
                });
            }
        }
    };

    $scope.deleteCategory = function (delid) {
        var deleteCat = window.confirm('Are you absolutely sure you want to delete?');

        if (deleteCat) {
            $http.post(urlseg + "productCategories/delete", {id: delid}).success(function (data) {
                $scope.allcategories = data;
                $scope.totalCategories = Object.keys($scope.allcategories).length;
                $scope.firstEntry = 1;
                $scope.lastEntry = perpage;

                $http.post(urlseg + 'productCategories/json_paginate', {startpoint: 0, perpage: perpage}).success(function (data) {
                    $scope.categories = data;
                });

                var recs = Object.keys(data).length; //Get number of products 

                if (recs <= perpage) {
                    $('.pagination_parent').hide();
                } else {
                    $('.pagination_parent').show();
                }

                var options = {
                    currentPage: 1,
                    totalPages: Math.ceil(recs / perpage),
                    alignment: 'right',
                    itemTexts: function (type, page, current) {
                        switch (type) {
                            case "first":
                                return "First";
                            case "prev":
                                return "Previous";
                            case "next":
                                return "Next";
                            case "last":
                                return "Last";
                            case "page":
                                return page;
                        }
                    },
                    onPageClicked: function (e, originalEvent, type, page) {
                        var startpoint = (page * perpage) - perpage;
                        $http.post(urlseg + 'productCategories/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function (data) {
                            $scope.categories = data;
                            $scope.firstEntry = startpoint + 1;
                            if ($scope.totalCategories < startpoint + perpage) {
                                $scope.lastEntry = $scope.totalCategories;
                            } else {
                                $scope.lastEntry = startpoint + perpage;
                            }
                        });
                    }
                };

                $('#pagination_div').bootstrapPaginator(options);
            });
        }
    };
});
