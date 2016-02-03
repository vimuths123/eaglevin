//Configurations
var urlseg = 'http://localhost:8000/'; //Host
var perpage = '9'; //Perpage for pagination





//Shopping cart functionalities(Start)

//Set No of Items in header cart
function setItems(array) {
    var json = $.parseJSON(array); //parsing response string into JSON Object
    var noOfItems = Object.keys(json).length; //A little bit crappy way to get JSON Object length, but it works
    if (noOfItems === 1) {
        $('.cart_quantity').html('<b>' + noOfItems + '</b> ITEM');
    } else {
        $('.cart_quantity').html('<b>' + noOfItems + '</b> ITEMS');
    }
}

//Return total price of items
function countTotal(array) {
    var price = 0;
    $.each($.parseJSON(array), function (key, value) {
        var subprice = parseInt(value.price) * parseInt(value.quantity);
        price += parseInt(subprice);
    });
    return price;
}


function createCart(array) {
    $(".cartHolder").html('');
    $(".cartHolder").append('<div class="cart_drop"> <span class="darw"></span><ul></ul></div>');

    $.each($.parseJSON(array), function (key, value) {
        $(".cartHolder ul").append('<li><img src="../images/mini_c_item1.png"><a href="#">' + value.title + '</a> <span class="price">$' + parseInt(value.price).toFixed(2) + ' x ' + value.quantity + '</span></li>');
    });

    $(".cartHolder ul").append('<div class="cart_bottom"><div class="subtotal_menu"><small style="padding: 0px;">Subtotal:</small><big>$' + countTotal(array).toFixed(2) + '</big></div><a href="/products/cart">View Cart</a></div>');
}

function cartInner(array) {
    if (array === 'null') {
//       $('.emptyCart').hide();
    } else {
        $('.emptyCart').hide();
        $.each($.parseJSON(array), function (key, value) {
            $(".cartInner").append('<tr class="productRow"><td width="10%"><img src="images/cart1.jpg"></td><td class="align_left" width="44%"><a class="pr_name" href="#">' + value.title + '</a></td><td class="align_center"><a class="edit editItm" pid="' + key + '">Edit</a></td><td class="align_center vline"><span class="price">$' + parseInt(value.price).toFixed(2) + '</span></td><td class="align_center vline"><input class="qty_box qty_' + key + '" type="text" value="' + value.quantity + '"></td><td class="align_center vline"><span class="price">$' + (parseInt(value.price) * parseInt(value.quantity)).toFixed(2) + '</span></td><td class="align_center vline"><a class="remove removeItm" pid="' + key + '"></a></td></tr>');
        });
    }
    $('.cartInner').trigger('create');

//  subTot
    $('.subTot').html('$' + countTotal(array).toFixed(2));
    $('.granTot').html('$' + countTotal(array).toFixed(2));
}

//{"166":{"quantity":"1","price":"7"},"165":{"quantity":"1","price":"7"}}

$(function () {
    $.post(urlseg + "cart/viewall", function (data) {
        cartInner(data);
        setItems(data);
        $('.cart_price').html('<b>$' + countTotal(data).toFixed(2) + '<b>');
        createCart(data);
    });

//    add to cart button on products page
    $(document).on('click', '#addToCart', function () {
        var id = $(this).attr('pid');
        var price = $(this).attr('price');
        var title = $(this).attr('title');
        var quantity = 1;

        $.post(urlseg + "cart/add", {pid: id, price: price, quantity: quantity, title: title}, function (data) {
            setItems(data);
            $('.cart_price').html('<b>$' + countTotal(data).toFixed(2) + '<b>');
            createCart(data);
        });
    });
    
//    add to cart button on single product page
    $(document).on('click', '#addToCartWitQuan', function () {
        var id = $(this).attr('pid');
        var price = $(this).attr('price');
        var title = $(this).attr('title');
        var quantity = $('.getQuantity').val();

        $.post(urlseg + "cart/add", {pid: id, price: price, quantity: quantity, title: title}, function (data) {
            setItems(data);
            $('.cart_price').html('<b>$' + countTotal(data).toFixed(2) + '<b>');
            createCart(data);
        });
    });

//     Continue shopping button
    $('.continue').click(function () {
        window.location.href = urlseg + "products";
    });

//    Checkout buuton
    $('.checkout').click(function () {
        window.location.href = urlseg + "products/checkout";
    });

//     Edit item button    
    $(document).on('click', '.editItm', function () {
        var quantity = $('.qty_' + $(this).attr('pid')).val();
        $.post(urlseg + "cart/update", {pid: $(this).attr('pid'), quantity: quantity}, function (data) {
            setItems(data);
            $('.cart_price').html('<b>$' + countTotal(data).toFixed(2) + '<b>');
            createCart(data);
        });
    });

//     Remove item from cart    
    $(document).on('click', '.removeItm', function () {
//        alert($(this).attr('pid'));
//        $('.row_'+$(this).attr('pid')).remove();
        $.post(urlseg + "cart/remove", {pid: $(this).attr('pid')}, function (data) {
            $('.productRow').remove();
            cartInner(data);
            setItems(data);
            $('.cart_price').html('<b>$' + countTotal(data).toFixed(2) + '<b>');
            createCart(data);
        });
    });
//Shopping cart functionalities(End)    


//    validate checkout form
    $('#checkoutForm').validate({
        rules: {
            fname: "required",
            lname: "required",
            country: "required",
            country_2: "required",
            company: "required",
            adderss: "required",
            adderss_2: "required",
            city: "required",
            city_2: "required",
            post_code: "required",
            postcode: "required",
            email: {
                required: true,
                email: true
            }
        }
    });
});


angular.module("prod_app", [], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).controller("prodController", function ($scope, $http, $timeout) {
    $scope.products = {};
    $scope.allproducts = {};
    $scope.totalProduct = 0;
    $scope.per_page = perpage; //Default itemsperpage
    $scope.filter_item = '1'; //Default filter
//    $scope.searchText = "";
//    $scope.category = 0;

    $scope.catUrl = "";
    $scope.searchUrl = "";
    $scope.catUrl = "";

    $scope.changeCategory = function (id) {
        $timeout(jQuery.uniform.update, 0);
        $scope.category = id;
        $scope.catUrl = "category=" + id;
        if (($scope.searchUrl == "") || ($scope.searchUrl == "search=")) {
            window.history.pushState("object or string", "Title", "/products?" + $scope.catUrl);
        } else {
            window.history.pushState("object or string", "Title", "/products?" + $scope.catUrl + "&" + $scope.searchUrl);
        }
        filter();
    };

//    Filter products by price(high/law), arrival
    $scope.filterchnge = function () {
        $timeout(jQuery.uniform.update, 0);
        filter();
    };

//    Search items
    $scope.search = function () {
        $scope.searchUrl = "search=" + $scope.searchText;
        if (($scope.searchUrl == "") || ($scope.searchUrl == "search=")) {
            window.history.pushState("object or string", "Title", "/products?" + $scope.catUrl);
        } else {
            window.history.pushState("object or string", "Title", "/products?" + $scope.catUrl + "&" + $scope.searchUrl);
        }
        filter();
    };

//    Change Items Per Page
    $scope.ChngeItmsPerPage = function () {
        $timeout(jQuery.uniform.update, 0);
        filter();
    };

    $scope.ChkFirst = 1;
    $scope.$watch('category', function () {
        if ($scope.ChkFirst === 1) {
            if ($scope.category !== 0) {
                $scope.catUrl = "category=" + $scope.category;
            }
            $scope.searchUrl = "search=" + $scope.searchText;
            filter();
        }
        $scope.ChkFirst = 0;
    });

    function filter() {
        $http.post(urlseg + "global/json_filterAllproducts", {category: $scope.category, searchedVal: $scope.searchText}).success(function (data) {
            $scope.allproducts = data;
            $scope.totalProduct = Object.keys($scope.allproducts).length;
            $scope.firstEntry = 1;
            $scope.lastEntry = $scope.per_page;
//        var per = perpage;
            $http.post(urlseg + 'global/json_filterstorepaginate', {category: $scope.category, searchedVal: $scope.searchText, startpoint: 0, perpage: $scope.per_page, filter_item: $scope.filter_item}).success(function (data) {
                $scope.products = data;
            });

            var recs = Object.keys(data).length; //Get number of products 

            if (recs <= $scope.per_page) {
                $('.pagination_parent').hide();
            } else {
                $('.pagination_parent').show();
            }

            var options = {
                currentPage: 1,
                totalPages: Math.ceil(recs / $scope.per_page),
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
                    var startpoint = (page * $scope.per_page) - $scope.per_page;
                    $http.post(urlseg + 'global/json_filterstorepaginate', {category: $scope.category, searchedVal: $scope.searchText, startpoint: startpoint, perpage: $scope.per_page, filter_item: $scope.filter_item}).success(function (data) {
                        $scope.products = data;
                        $scope.firstEntry = startpoint + 1;
                        if ($scope.totalProduct < startpoint + $scope.per_page) {
                            $scope.lastEntry = $scope.totalProduct;
                        } else {
                            $scope.lastEntry = startpoint + $scope.per_page;
                        }
                    });
                }
            };

            $('#pagination_div').bootstrapPaginator(options);
        });
    }

});

