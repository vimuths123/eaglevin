//Configurations
var urlseg = 'http://localhost:8000/'; //Host
var perpage = 8; //Perpage for pagination


$(function() {
//Show success notification

    $('.closer').click(function() {
        $('.suc_msg').hide();
    });

    $('.add_product').validate({
        rules: {
            pro_title: "required",
            price: {
                required: true,
                number: true,
                min: 0.01
            },
            quantity: {
                number: true,
                min: 1
            },
            category: {
                required: true,
            }
        }
    });

    $('.addPros').validate({
        rules: {
            quantity: {
                required: true,
                number: true,
                min: 1
            }
        }
    });

    $('.add_product').submit(function(e) {
        e.preventDefault();
    });


});

angular.module("product_app", [], function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).controller("addController", function($scope, $timeout) {
    $scope.addForm = {};
    $scope.addForm.status = "1";
    $scope.addForm.stock = "1";
    $scope.addForm.isfeatured = 0;



    $scope.clear = function() {
        //Select tag cannot set default without this
        $timeout(jQuery.uniform.update, 0);

        $scope.addForm.productTitle = "";
        $scope.addForm.description = "";
        $scope.addForm.price = "";
        $scope.addForm.category = "";
        $scope.addForm.isfeatured = 0;
        $scope.addForm.status = "1";
        $scope.addForm.stock = "1";
        $scope.addForm.quantity = "";
    };

    $scope.save = function() {
        if ($('.add_product').valid())
        {

            //Add the form to FormData object
            var formData = new FormData($(".add_product")[0]);


            //Select tag cannot set default without this
            $timeout(jQuery.uniform.update, 0);

            var fdata = JSON.stringify($scope.addForm, null, 4);

            //append angular js model values
            formData.append('object', fdata);

            //send values with ajax
            $.ajax({
                type: 'POST',
                url: urlseg + 'products/json_add',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    if (data === "true") {
                        $('.ui-pnotify').show();
                    }
                }
            });


            //Set input fields to default
            $scope.addForm.productTitle = "";
            $scope.addForm.description = "";
            $scope.addForm.price = "";
            $scope.addForm.category = "";
            $scope.addForm.isfeatured = "";
            $scope.addForm.isfeatured = 0;
            $scope.addForm.status = "1";
            $scope.addForm.stock = "1";
            $scope.addForm.quantity = "";

            //Stop giving error messages after submitting
            $(".add_product").validate().cancelSubmit = true;
        }

    };
});

angular.module("update_app", [], function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).controller("updateController", function($scope, $timeout) {
    $scope.addForm = {};
    $scope.addForm.id = 0;
    $scope.addForm.status = "1";
    $scope.addForm.stock = "1";
//    $scope.addForm.isfeatured = 0;
    $scope.addForm.isfeatured = 0;


    $scope.clear = function() {
        //Select tag cannot set default without this
        $timeout(jQuery.uniform.update, 0);

        $scope.addForm.productTitle = "";
        $scope.addForm.description = "";
        $scope.addForm.price = "";
        $scope.addForm.category = "";
        $scope.addForm.isfeatured = 0;
        $scope.addForm.status = "1";
        $scope.addForm.stock = "1";
        $scope.addForm.quantity = "";
    };

    $scope.save = function() {
        if ($('.add_product').valid())
        {

            //Add the form to FormData object
            var formData = new FormData($(".add_product")[0]);

            //Select tag cannot set default without this
            $timeout(jQuery.uniform.update, 0);

            var fdata = JSON.stringify($scope.addForm, null, 4);

            //append angular js model values
            formData.append('object', fdata);

            //send values with ajax
            $.ajax({
                type: 'POST',
                url: urlseg + 'products/json_update',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    if (data === "true") {
                        $('.ui-pnotify').show();
                    }
                }
            });


//            //Set input fields to default
//            $scope.addForm.productTitle = "";
//            $scope.addForm.description = "";
//            $scope.addForm.price = "";
//            $scope.addForm.category = "";
//            $scope.addForm.isfeatured = "";
//            $scope.addForm.isfeatured = 0;
//            $scope.addForm.status = "1";
//            $scope.addForm.stock = "1";

            //Stop giving error messages after submitting
            $(".add_product").validate().cancelSubmit = true;
        }

    };
});

angular.module("view_app", ["checklist-model"], function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).controller("viewController", function($scope, $http) {
    $scope.products = {};
    $scope.allproducts = {};
    $scope.totalProduct = 0;
//    $scope.firstEntry = 0;
//    $scope.lastEntry = 0;

//<-----------Get products to data table------------------>
    $http.post(urlseg + "products/getall").success(function(data) {
        $scope.allproducts = data;
        $scope.totalProduct = Object.keys($scope.allproducts).length;
        $scope.firstEntry = 1;
        $scope.lastEntry = perpage;
//        var per = perpage;
        $http.post(urlseg + 'products/json_paginate', {startpoint: 0, perpage: perpage}).success(function(data) {
            $scope.products = data;
        });

        var recs = Object.keys(data).length; //Get number of products 

//        Hide pagination if no products
        if (recs <= perpage) {
            $('.pagination_parent').hide();
        } else {
            $('.pagination_parent').show();
        }

        var options = {
            currentPage: 1,
            totalPages: Math.ceil(recs / perpage),
            alignment: 'right',
            itemTexts: function(type, page, current) {
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
            onPageClicked: function(e, originalEvent, type, page) {
                var startpoint = (page * perpage) - perpage;
                $http.post(urlseg + 'products/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function(data) {
                    $scope.products = data;

//                    for show pagination details
                    $scope.firstEntry = startpoint + 1;
                    if ($scope.totalProduct < startpoint + perpage) {
                        $scope.lastEntry = $scope.totalProduct;
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
    $scope.search = function() {
        $http.post(urlseg + "products/jason_searchAll", {searchedVal: $scope.searchText}).success(function(data) {
            $scope.allproducts = data;
            $scope.totalProduct = Object.keys($scope.allproducts).length;
            $scope.firstEntry = 1;
            $scope.lastEntry = perpage;
//        var per = perpage;
            $http.post(urlseg + 'products/json_searchPaginate', {searchedVal: $scope.searchText, startpoint: 0, perpage: perpage}).success(function(data) {
                $scope.products = data;
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
                itemTexts: function(type, page, current) {
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
                onPageClicked: function(e, originalEvent, type, page) {
                    var startpoint = (page * perpage) - perpage;
                    $http.post(urlseg + 'products/json_searchPaginate', {searchedVal: $scope.searchText, startpoint: startpoint, perpage: perpage}).success(function(data) {
                        $scope.products = data;
                        $scope.firstEntry = startpoint + 1;
                        if ($scope.totalProduct < startpoint + perpage) {
                            $scope.lastEntry = $scope.totalProduct;
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

    $scope.chkedProducts = {
        ids: []
    };
    var ids = $scope.chkedProducts.ids;

    $scope.idchk = function() {
        alert(JSON.stringify(ids));
    };

//<-----------delete multiple products at once------------------>   
    $scope.allDelete = function() {
        var deleteUser = window.confirm('Are you absolutely sure you want to delete?');
        
        if (deleteUser) {
            if (!ids.length) {
                alert("You haven't select any product to delete..");
            } else {
                $http.post(urlseg + "products/multiDelete", {ids: $scope.chkedProducts.ids}).success(function(data) {
                    $scope.allproducts = data;
                    $scope.totalProduct = Object.keys($scope.allproducts).length;
                    $scope.firstEntry = 1;
                    $scope.lastEntry = perpage;
                    $http.post(urlseg + 'products/json_paginate', {startpoint: 0, perpage: perpage}).success(function(data) {
                        $scope.products = data;
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
                        itemTexts: function(type, page, current) {
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
                        onPageClicked: function(e, originalEvent, type, page) {
                            var startpoint = (page * perpage) - perpage;
                            $http.post(urlseg + 'products/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function(data) {
                                $scope.products = data;
                                $scope.firstEntry = startpoint + 1;
                                if ($scope.totalProduct < startpoint + perpage) {
                                    $scope.lastEntry = $scope.totalProduct;
                                } else {
                                    $scope.lastEntry = startpoint + perpage;
                                }
                            });
                        }
                    };

                    $('#pagination_div').bootstrapPaginator(options);
                });
                
                $scope.chkedProducts.ids = [];
            }
        }
    };
//<-----------delete multiple products at once------------------>   
    $scope.noProdoct = false;
    $scope.updateSuccess = false;
    $scope.itemAddedSuccess = false;
    $scope.updateVals = {};

    $scope.editClick = function() {
        $scope.updateSuccess = false;
//        alert(JSON.stringify(ids));
        if (!ids.length) {
            $scope.noProdoct = true;
        } else {
            $scope.noProdoct = false;
        }
    };

    $scope.addItemsClick = function() {
        $scope.itemAddedSuccess = false;

        if (!ids.length) {
            $scope.noProdoct = true;
        } else {
            $scope.noProdoct = false;
        }
    };

    $scope.multiEdit = function() {
        if (!ids.length) {

        } else {
            var updates = $scope.updateVals;
            $http.post(urlseg + "products/multiUpdate", {ids: ids, valeusToUpdate: updates}).success(function(data) {
                $scope.allproducts = data;
                $scope.totalProduct = Object.keys($scope.allproducts).length;
                $scope.firstEntry = 1;
                $scope.lastEntry = perpage;
                $http.post(urlseg + 'products/json_paginate', {startpoint: 0, perpage: perpage}).success(function(data) {
                    $scope.products = data;
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
                    itemTexts: function(type, page, current) {
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
                    onPageClicked: function(e, originalEvent, type, page) {
                        var startpoint = (page * perpage) - perpage;
                        $http.post(urlseg + 'products/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function(data) {
                            $scope.products = data;
                            $scope.firstEntry = startpoint + 1;
                            if ($scope.totalProduct < startpoint + perpage) {
                                $scope.lastEntry = $scope.totalProduct;
                            } else {
                                $scope.lastEntry = startpoint + perpage;
                            }
                        });
                    }
                };

                $('#pagination_div').bootstrapPaginator(options);
            });
            $scope.updateSuccess = true;
            $scope.searchText = '';
        }
    };
    $scope.multiAddItems = function() {
        if (!ids.length) {

        } else {
            if ($('.addPros').valid()) {
                var addItems = $scope.addItems;
                $http.post(urlseg + "products/multiAddItems", {ids: ids, addItems: addItems}).success(function(data) {
                    $scope.allproducts = data;
                    $scope.totalProduct = Object.keys($scope.allproducts).length;
                    $scope.firstEntry = 1;
                    $scope.lastEntry = perpage;
                    $http.post(urlseg + 'products/json_paginate', {startpoint: 0, perpage: perpage}).success(function(data) {
                        $scope.products = data;
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
                        itemTexts: function(type, page, current) {
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
                        onPageClicked: function(e, originalEvent, type, page) {
                            var startpoint = (page * perpage) - perpage;
                            $http.post(urlseg + 'products/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function(data) {
                                $scope.products = data;
                                $scope.firstEntry = startpoint + 1;
                                if ($scope.totalProduct < startpoint + perpage) {
                                    $scope.lastEntry = $scope.totalProduct;
                                } else {
                                    $scope.lastEntry = startpoint + perpage;
                                }
                            });
                        }
                    };

                    $('#pagination_div').bootstrapPaginator(options);
                });
                $scope.itemAddedSuccess = true;
                $scope.searchText = '';
            }
        }
    };


    $scope.deleteProduct = function(delId) {
        var deleteUser = window.confirm('Are you absolutely sure you want to delete?');

        if (deleteUser) {
            $http.post(urlseg + "products/delete", {id: delId}).success(function(data) {
                $scope.allproducts = data;
                $scope.totalProduct = Object.keys($scope.allproducts).length;
                $scope.firstEntry = 1;
                $scope.lastEntry = perpage;
                $http.post(urlseg + 'products/json_paginate', {startpoint: 0, perpage: perpage}).success(function(data) {
                    $scope.products = data;
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
                    itemTexts: function(type, page, current) {
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
                    onPageClicked: function(e, originalEvent, type, page) {
                        var startpoint = (page * perpage) - perpage;
                        $http.post(urlseg + 'products/json_paginate', {startpoint: startpoint, perpage: perpage}).success(function(data) {
                            $scope.products = data;
                            $scope.firstEntry = startpoint + 1;
                            if ($scope.totalProduct < startpoint + perpage) {
                                $scope.lastEntry = $scope.totalProduct;
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
