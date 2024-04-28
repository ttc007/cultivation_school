// var user = DemoApp.initDataUnsafe.user;
// document.getElementById('user_id').innerHTML = user.first_name + " " + user.last_name;
const DemoApp = {
    initData      : Telegram.WebApp.initData || '',
    initDataUnsafe: Telegram.WebApp.initDataUnsafe || {},
    MainButton    : Telegram.WebApp.MainButton,

    init(options) {
        document.body.style.visibility = '';
        Telegram.WebApp.ready();
        Telegram.WebApp.MainButton.setParams({
            text      : 'CLOSE WEBVIEW',
            is_visible: true
        }).onClick(DemoApp.close);
    },
    expand() {
        Telegram.WebApp.expand();
    },
    close() {
        Telegram.WebApp.close();
    },
}

var teleUser = DemoApp.initDataUnsafe.user;
var tele_id = 1295978402;
var tele_name = "@cong1909";

var address = null;
var tonConnectUI = null;
var connect = false;

// Function to call API to increase ore after every 5 seconds
function increaseOre() {
    var dataToSend = {
        user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
        type: 'ore', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
    };

    $.ajax({
        type: "POST",
        url: "api/resources.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify(dataToSend),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            $("#ore_quantity").text(response.ore);

            $('#oreCount').addClass('animateOreCount');
            $("#oreCount").text("+" + response.quantity);
            // Biến mất sau 2 giây
            setTimeout(function() {
                $('#oreCount').removeClass('animateOreCount');
            }, 2000);
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function initDataUser() {
    $.ajax({
        type: "POST",
        url: "api/resources.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_ore', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            $("#ore_quantity").text(response.ore);

            if (response.wallet) {
                $("#wallet").text(catChuoi(response.wallet, 22));
                $("#connect_wallet_button").text("Change");
            }
            $("#tele_user").text("User: " + tele_name);
            console.log(teleUser);

        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function showStudentListPopup() {
    $.ajax({
        type: "POST",
        url: "api/student.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_user_students', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            var popup = document.getElementById("studentListPopup");
            popup.style.display = "block";

            // Tạo biến để lưu chuỗi HTML của danh sách học viên
            $("#student_list").text('');

            // Duyệt qua từng phần tử trong dữ liệu response và thêm thông tin của từng học viên vào chuỗi HTML
            for (var key in response) {
                var student = response[key];

                var studentListHTML = '<div class="card">';
                studentListHTML += '<img src="img/' + student.id + '.png" alt="' + student.ten + '">';
                studentListHTML += '<p>Tên: ' + student.name + '</p>';
                studentListHTML += '<p>Cấp bậc: ' + student.level + '</p>';
                studentListHTML += '<p>Phẩm chất: ' + student.quality + '</p>';
                studentListHTML += '<p>Thiên phú: ' + student.innate + '</p>';
                studentListHTML += '<p>Chiến lực: ' + student.power + '</p>';
                studentListHTML += '<button onclick="upgradeStudent(' + student.user_student_id +')">Nâng cấp: ' 
                + student.exp + ' ore</button>';

                studentListHTML += '<div style="position:absolute; left:210px; top:10px">';
                studentListHTML += '<img src="img/vokiem.png" style="width:50px" onClick="pickItemPopuoShow(`Kiếm`, '+student.user_student_id+')" id="item_equipment_kiem'+student.user_student_id+'"/>';
                studentListHTML += '<img src="img/voao.png" style="width:50px; margin-left:7px" onClick="pickItemPopuoShow(`Áo`, '+student.user_student_id+')" id="item_equipment_ao'+student.user_student_id+'"/>';
                studentListHTML += '<img src="img/voco.png" style="width:50px;" onClick="pickItemPopuoShow(`Cờ`, '+student.user_student_id+')" id="item_equipment_co'+student.user_student_id+'"/>';
                studentListHTML += '<img src="img/votran.png" style="width:50px; margin-left:7px"onClick="pickItemPopuoShow(`Trận pháp`, '+student.user_student_id+')" id="item_equipment_tran'+student.user_student_id+'" />';
                studentListHTML += ' </div>';
                studentListHTML += '</div>';

                var html = $(studentListHTML);
                $("#student_list").append(html);

                for (var key1 in student.items) { 
                    var item = student.items[key1];
                    if (item.type_equipment == 'Kiếm') {
                        $("#item_equipment_kiem" + student.user_student_id).attr('src', "img/item/"+item.id+".png");
                    } else if  (item.type_equipment == 'Áo') {
                        $("#item_equipment_ao" + student.user_student_id).attr('src', "img/item/"+item.id+".png");
                    } else if  (item.type_equipment == 'Cờ') {
                        $("#item_equipment_co" + student.user_student_id).attr('src', "img/item/"+item.id+".png");
                    } else if  (item.type_equipment == 'Trận pháp') {
                        $("#item_equipment_tran" + student.user_student_id).attr('src', "img/item/"+item.id+".png");
                    }
                }
                $("#total_power").text(student.user_power);
            }
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
    
}

function pickItemPopuoShow(type_equipment, user_student_id) {
    $.ajax({
        type: "POST",
        url: "api/bag.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_item_equipment',
            type_equipment: type_equipment
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            // Tạo biến để lưu chuỗi HTML của danh sách học viên
            var studentListHTML = '';

            // Duyệt qua từng phần tử trong dữ liệu response và thêm thông tin của từng học viên vào chuỗi HTML
            for (var key in response) {
                var item = response[key];
                studentListHTML += '<div class="card" style="width:77px" onClick="changeItemEquipment('+item.id+', '+ user_student_id +', `'+item.type_equipment+'`)">';
                studentListHTML += '<img src="img/item/' + item.id + '.png" style="width:100%"/>';
                studentListHTML += '<div style="height:40px"><b> '+ item.name +'</b></div>';
                studentListHTML += '<p>Chiến lực: <span style="color:#e2711a">'+ item.power +'</span></p>';
                studentListHTML += '<p>Đang có: '+ item.quantity +'</p>';
                studentListHTML += '</div>';
            }
            // Đưa chuỗi HTML vào div có id là "student_list"
            document.getElementById('equipment_item').innerHTML = studentListHTML;
            $("#equipmentPopup").css('display', 'block');
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
    
}

function changeItemEquipment(item_id,user_student_id,type_equipment) {
    $.ajax({
        type: "POST",
        url: "api/bag.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'equipment',
            type_equipment: type_equipment,
            item_id:item_id,
            user_student_id:user_student_id
        }),
        contentType: "application/json",
        success: function(response) {
            showStudentListPopup();
            $("#equipmentPopup").css('display', 'none');
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function closeEquipmentPopup() {
    $("#equipmentPopup").css('display', 'none');
}

function upgradeStudent(user_student_id) {
    $.ajax({
        type: "POST",
        url: "api/student.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'upgrade_student',
            user_student_id:user_student_id
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            if (!response.msg) {
                showStudentListPopup();
                $("#ore_quantity").text(response.ore);          
            } else {
                var popup = document.getElementById("msgPopup");
                $("#msg").text(response.msg);
                popup.style.display = "block";
            }
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function showRecruitmentPopup() {
    $.ajax({
        type: "POST",
        url: "api/student.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'recuit_student', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            $("#recuit_student_name").text(response.name);
            $("#recuit_student_quality").text("Phẩm chất:" + response.quality);
            $("#recuit_student_innate").text("Thiên phú:" + response.innate);

            $("#recuit_student_img").attr('src', "img/" + response.recuit_student_id + ".png");

            $("#recuit_student_btn").text("Tuyển mộ:  " + response.cost + " Ore");
            
            var popup = document.getElementById("recruitmentPopup");
            popup.style.display = "block";
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function closePopup() {
    var popups = document.getElementsByClassName("popup");
    for (var i = 0; i < popups.length; i++) {
        popups[i].style.display = "none";
    }
}

function closeMsgPopup() {
    var popups = document.getElementsByClassName("msgPopup");
    for (var i = 0; i < popups.length; i++) {
        popups[i].style.display = "none";
    }
}

function recuitStudentRenew() {
    $.ajax({
        type: "POST",
        url: "api/student.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'recuit_student_renew', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            if (!response.msg) {
                $("#recuit_student_name").text(response.name);
                $("#recuit_student_quality").text("Phẩm chất:" + response.quality);
                $("#recuit_student_innate").text("Thiên phú:" + response.innate);

                $("#recuit_student_img").attr('src', "img/" + response.recuit_student_id + ".png");

                $("#recuit_student_btn").text("Tuyển mộ: " + response.cost + " Ore");

                $("#ore_quantity").text(response.ore);
                var popup = document.getElementById("recruitmentPopup");
                popup.style.display = "block";
            } else {
                var popup = document.getElementById("msgPopup");
                $("#msg").text(response.msg);
                popup.style.display = "block";
            }
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function recuitStudentWithCost() {
    $.ajax({
        type: "POST",
        url: "api/student.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'recuit_student_with_cost', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            if (!response.msg) {
                $("#recuit_student_name").text(response.name);
                $("#recuit_student_quality").text("Phẩm chất:" + response.quality);
                $("#recuit_student_innate").text("Thiên phú:" + response.innate);

                $("#recuit_student_img").attr('src', "img/" + response.recuit_student_id + ".png");

                $("#recuit_student_btn").text("Tuyển mộ " + response.cost + " Ore");

                $("#ore_quantity").text(response.ore);
                var popup = document.getElementById("recruitmentPopup");
                popup.style.display = "none";
                var popup = document.getElementById("msgPopup");
                $("#msg").text("Tuyển mộ thành công!");
                popup.style.display = "block";

                showStudentListPopup();
            } else {
                var popup = document.getElementById("msgPopup");
                $("#msg").text(response.msg);
                popup.style.display = "block";
            }
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

async function connectToWallet() {
    if (!tonConnectUI) {
        tonConnectUI = new TON_CONNECT_UI.TonConnectUI({
            manifestUrl: 'https://vayugo.000webhostapp.com/tonconnect-manifest.json',
            buttonRootId: 'ton-connect'
        });
    }

    if (connect) {
        await tonConnectUI.disconnect();
        connect = false;
    }

    var connectedWallet = await tonConnectUI.connectWallet();
    // Do something with connectedWallet if needed
    address = connectedWallet.account.address;
    console.log(connectedWallet);
    connect = true;
    $.ajax({
        type: "POST",
        url: "api/wallet.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'connect_wallet', 
            wallet:address,
        }),
        contentType: "application/json",
        success: function(response) {
            initDataUser();
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

//0:23be4ae23d26df3a4843393306bd65fa5263aabd3f67dbb23a326d70a6be4941
//UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH
async function sendTon() {
    if (!tonConnectUI) {
        tonConnectUI = new TON_CONNECT_UI.TonConnectUI({
            manifestUrl: 'https://vayugo.000webhostapp.com/tonconnect-manifest.json',
            buttonRootId: 'ton-connect'
        });
    }

    console.log(connect);
    if (!connect) {
        const connectedWallet = await tonConnectUI.connectWallet();
        connect = true;
        address = connectedWallet.account.address;
        console.log("Connect");
    }

    var hesoNhan = $("#hesoNhan").attr("data-value");
    const result = await tonConnectUI.sendTransaction({
      messages: [
        {
          address: "UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH",
          amount: BigInt(0.1 * 1000000000 * hesoNhan).toString(),
        }
      ],
      validUntil: Date.now() + 5 * 60 * 1000, // 5 minutes for user to approve
    });

    if (result) {
        $.ajax({
            type: "POST",
            url: "api/transaction.php", // Đường dẫn tới file xử lý API
            data: JSON.stringify({
                user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
                type: 'check_transaction', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
                amount: 1000 * hesoNhan,
                address:address
            }),
            contentType: "application/json",
            success: function(response) {
                var response = JSON.parse(response);
                $("#ore_quantity").text(response.ore);
                console.log(result);
            },
            error: function(xhr, status, error) {
                console.error("API error:", error);
            }
        });
    }
}

function catChuoi(chuoi, doDai) {
    if (chuoi.length > doDai) {
        var doDaiCat = (doDai - 3) / 2; // Độ dài mỗi phần cần cắt, trừ đi 3 cho dấu "..."
        var phanDau = chuoi.slice(0, Math.ceil(doDaiCat)); // Lấy phần đầu của chuỗi
        var phanCuoi = chuoi.slice(-Math.floor(doDaiCat)); // Lấy phần cuối của chuỗi
        return phanDau + "..." + phanCuoi; // Trả về chuỗi đã cắt
    }
    return chuoi; // Trả về chuỗi ban đầu nếu không cần cắt
}

function hesoNhan() {
    var hesoNhan = $("#hesoNhan").attr("data-value");
    hesoNhan++;
    if (hesoNhan > 10) {
        hesoNhan = 1;
    }

    $("#hesoNhan").attr("data-value", hesoNhan);
    $("#hesoNhan").text("x" + hesoNhan);

    var text = 'Nạp '+(1000 * hesoNhan)+'<img src="img/ore.png" class="symbol"> = '+  (0.1 * hesoNhan).toFixed(1) 
    +'<img src="img/ton.png" class="symbol"/>';
    $("#btn_sendTon").text('');
    $("#btn_sendTon").append(text);
}

function start() {
    $("#btn_connectWallet").css("display", "none");
    $(".mining-container").css("display", "flex");

    setInterval(increaseOre, 5000);
    initDataUser();
}

function showCombatPopup() {
    $.ajax({
        type: "POST",
        url: "api/map.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_maps', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            // Tạo biến để lưu chuỗi HTML của danh sách học viên
            var mapListHTML = '';

            // Duyệt qua từng phần tử trong dữ liệu response và thêm thông tin của từng học viên vào chuỗi HTML
            for (var key in response) {
                var map = response[key];

                mapListHTML += '<div class="card cardMap" onclick="startCombat('+ map.id +')">';
                mapListHTML += '<img src="img/map/' + map.id + '.png" alt="' + map.name + '" style="width:100%">';
                mapListHTML += '<p>Tên: ' + map.name + '</p>';
                mapListHTML += '<p>Yêu cầu chiến lực: ' + map.power_required + '</p>';
                mapListHTML += '<p>Yêu cầu vật phẩm: ' + map.requireds + "</p>";
                mapListHTML += '<p>Rơi: ' + map.items + "</p>";

                mapListHTML += '</div>';
            }

            // Đưa chuỗi HTML vào div có id là "student_list"
            document.getElementById('map_list').innerHTML = mapListHTML;
            var popup = document.getElementById("combatPopup");
            popup.style.display = "block";
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

var combatInterval = null;
function startCombat(mapId) {
    $.ajax({
        type: "POST",
        url: "api/map.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'combat',
            map_id: mapId
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            if (!response.msg) {
                // Tạo biến để lưu chuỗi HTML của danh sách học viên
                var mapListHTML = '<div class="card">';
                mapListHTML += '<p>Đang lịch luyện...</p>';
                mapListHTML += `<p>Vui lòng không đóng cửa sổ lịch luyện.
                 Các học viên cần được bạn giám xác trong quá trình lịch luyện. 
                 Nếu bạn đóng cửa sổ lịch luyện các học viên sẽ tự động trở về. 
                 Hãy đóng cửa sổ lịch luyện khi bạn đã thu thập đủ vật phẩm cần thiết.</p>`;
                mapListHTML += '<img src="img/map/' + mapId + '.png" style="width:100%;margin-top:10px">';

                mapListHTML += '<img src="img/combat.gif" id="combatGif" >';

                mapListHTML += '<h3 style="margin:4px 0">Danh sách vật phẩm thu thập:</h3>';
                mapListHTML += '<div id="item_collect"></div>';
                mapListHTML += '</div>';

                document.getElementById('map_list').innerHTML = mapListHTML;

                combatInterval = setInterval(function(){
                    $.ajax({
                        type: "POST",
                        url: "api/map.php", // Đường dẫn tới file xử lý API
                        data: JSON.stringify({
                            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
                            type: 'combat_collect',
                            map_id: mapId
                        }),
                        contentType: "application/json",
                        success: function(response) {
                            var response = JSON.parse(response);

                            // Tạo một đối tượng Date, đại diện cho thời gian hiện tại
                            var currentDate = new Date();

                            // Lấy thông tin về ngày, tháng, năm, giờ, phút, giây
                            var day = currentDate.getDate();
                            var month = currentDate.getMonth() + 1; // Lưu ý: Tháng bắt đầu từ 0
                            var year = currentDate.getFullYear();
                            var hours = currentDate.getHours();
                            var minutes = currentDate.getMinutes();
                            var seconds = currentDate.getSeconds();

                            if (seconds < 10) {
                                seconds = '0' + seconds;
                            }
                            // Định dạng thời gian để hiển thị
                            var formattedDate = day + '/' + month + '/' + year;
                            var formattedTime = hours + ':' + minutes + ':' + seconds;

                            // Hiển thị thời gian và ngày
                            var itemCollect = "<div>"+ formattedTime
                                + ": Bạn thu thập được <img src='img/item/" + response.id + ".png'  class='item-img'>" 
                                + response.name + "</div>";
                            $("#item_collect").prepend(itemCollect);
                        },
                        error: function(xhr, status, error) {
                            console.error("API error:", error);
                        }
                    });
                }, 5000);
                $("#combatCloseBtn").click(function() {
                    clearInterval(combatInterval);
                });
            } else {
                var popup = document.getElementById("msgPopup");
                $("#msg").text(response.msg);
                popup.style.display = "block";
            }
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function closeCombatPopup() {
    clearInterval(combatInterval);
    var popup = document.getElementById("combatPopup");
    popup.style.display = "none";
}
function showBagPopup() {
    $.ajax({
        type: "POST",
        url: "api/item.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_bag', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            // Tạo biến để lưu chuỗi HTML của danh sách học viên
            $("#user_bag").text('');
            // Duyệt qua từng phần tử trong dữ liệu response và thêm thông tin của từng học viên vào chuỗi HTML
            for (var key in response) {
                var item = response[key];

                var bagListHTML = '';

                bagListHTML += '<div class="card cardItem">';
                bagListHTML += '<img src="img/item/' + item.item_id + '.png" style="width:100%">';
                bagListHTML += '<div style="width:100%;height:40px"><b>' + item.item_name + '</b></div>';
                bagListHTML += '<p>Số lượng: ' + item.quantity + '</p>';
                bagListHTML += '</div></div>';

                var bag = $(bagListHTML);

                var sellBtn = $('<button style="margin:5px 5px 5px 0px" onclick="sellPopupRender('+item.user_item_id+')">Bán</button>');
                bag.append(sellBtn);

                var sellMarketBtn = $('<button style="margin:5px 5px 5px 0px" onclick="sellMarket('+item.user_item_id+')">Bán<img src="img/trade.png" style="width:18px;margin-bottom: -4px;margin-left:2px" /></button>');
                bag.append(sellMarketBtn);
                $("#user_bag").append(bag);
            }

            var popup = document.getElementById("bagPopup");
            popup.style.display = "block";
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function closeSellPopup() {
    var popup = document.getElementById("sellPopup");
    popup.style.display = "none";
}

function closeBuyPopup() {
    var popup = document.getElementById("buyPopup");
    popup.style.display = "none";
}

function sellPopupRender(user_item_id) {
    $.ajax({
        type: "POST",
        url: "api/item.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_user_item', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
            user_item_id:user_item_id
        }),
        contentType: "application/json",
        success: function(response) {
            var item = JSON.parse(response);

            var popup = document.getElementById("sellPopup");
            popup.style.display = "block";

            $("#sellPopupTitle").text('Bán');

            $("#sell_item").text('');
            var sellImg = '';

            sellImg += '<div style="float;left;width:50%">';
            sellImg += '<img src="img/item/' + item.item_id + '.png" style="width:100%">';
            sellImg += '</div>';

            var img = $(sellImg);
            $("#sell_item").append(img);

            var rightDiv = $("<div style='float:left;width:45%'></div>");
            rightDiv.append("<b>"+item.item_name+"</b>");
            var inputQuantity = $('<input id="quantitySell" type="number" min="1" value="1"/>');
            inputQuantity.attr('max', item.quantity);
            rightDiv.append(inputQuantity);



            rightDiv.append('<span> / ' + item.quantity + '</span>');

            var oreSell = $("<p>Thu về:" + item.cost +" ore</p>");
            rightDiv.append(oreSell);

            inputQuantity.change(function() {
                oreSell.text("Thu về:" + (item.cost * $(this).val()) +" ore")
            });

            var btnSell = $("<button onclick='sell("+user_item_id+")'>Bán</button>");
            rightDiv.append(btnSell);
            $("#sell_item").append(rightDiv);
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function sell(user_item_id) {
    var quantity = $("#quantitySell").val();
    $.ajax({
        type: "POST",
        url: "api/item.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'sell', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
            user_item_id:user_item_id,
            quantity:quantity
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            if (response.msg) {
                var popup = document.getElementById("msgPopup");
                $("#msg").text(response.msg);
                popup.style.display = "block";
            } else {
                closeSellPopup();
                showBagPopup();
                $("#ore_quantity").text(response.ore);
            }
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function showShopPopup() {
    $.ajax({
        type: "POST",
        url: "api/item.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_items', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            // Tạo biến để lưu chuỗi HTML của danh sách học viên
            $("#shop_list").text('');
            // Duyệt qua từng phần tử trong dữ liệu response và thêm thông tin của từng học viên vào chuỗi HTML
            for (var key in response) {
                var item = response[key];

                var shopListHTML = '';

                shopListHTML += '<div class="card cardItem">';
                shopListHTML += '<img src="img/item/' + item.id + '.png" style="width:100%">';
                shopListHTML += '<div style="width:100%;height:40px"><b>' + item.name + '</b></div>';
                shopListHTML += '<div style="width:100%;height:20px">Giá:' + (item.cost*2) + ' Ore</div>';
                shopListHTML += '</div>';

                var shop = $(shopListHTML);

                var buyBtn = $('<button style="margin:5px 5px 5px 0px" onclick="buyPopupRender('+item.id+')">Mua</button>');

                shop.append(buyBtn);
                $("#shop_list").append(shop);
            }

            var popup = document.getElementById("shopPopup");
            popup.style.display = "block";
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function buyPopupRender(id) {
    $.ajax({
        type: "POST",
        url: "api/item.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_item', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
            id:id
        }),
        contentType: "application/json",
        success: function(response) {
            var item = JSON.parse(response);

            var popup = document.getElementById("buyPopup");
            popup.style.display = "block";

            $("#buy_item").text('');
            var sellImg = '';

            sellImg += '<div style="float;left;width:50%">';
            sellImg += '<img src="img/item/' + item.id + '.png" style="width:100%">';
            sellImg += '</div>';

            var img = $(sellImg);
            $("#buy_item").append(img);

            var rightDiv = $("<div style='float:left;width:45%'></div>");
            rightDiv.append("<b>"+item.name+"</b>");
            var inputQuantity = $('<input id="quantityBuy" type="number" min="1" value="1" style="width:50%"/>');
            rightDiv.append(inputQuantity);

            var oreSell = $("<p>Tiêu hao:" + (item.cost * 2) +" Ore</p>");
            rightDiv.append(oreSell);

            inputQuantity.change(function() {
                oreSell.text("Tiêu hao:" + (item.cost * 2 * $(this).val()) +" ore")
            });

            var btnSell = $("<button onclick='buy("+id+")'>Mua</button>");
            rightDiv.append(btnSell);
            $("#buy_item").append(rightDiv);
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function buy(item_id) {
    var quantity = $("#quantityBuy").val();
    $.ajax({
        type: "POST",
        url: "api/item.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'buy', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
            item_id:item_id,
            quantity:quantity
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            if (response.msg) {
                var popup = document.getElementById("msgPopup");
                $("#msg").text(response.msg);
                popup.style.display = "block";
            } else {
                var popup = document.getElementById("shopPopup");
                popup.style.display = "none";
                var popup = document.getElementById("buyPopup");
                popup.style.display = "none";

                showShopPopup();
                $("#ore_quantity").text(response.ore);
                var popup = document.getElementById("msgPopup");
                $("#msg").text("Mua thành công!");
                popup.style.display = "block";
            }
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

function showMedicinePopup(type_craff) {
    console.log(type_craff);
    
    $.ajax({
        type: "POST",
        url: "api/item.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'get_item_medicine', 
            type_craff:type_craff
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            // Tạo biến để lưu chuỗi HTML của danh sách học viên
            $("#medicine_list").text('');

            var type_innate = '';
            console.log(type_craff);
            if (type_craff == 'Đan dược') {
                $("#craff_title").text('Luyện đan');
                $("#craff_alt").text('Chọn đan dược muốn luyện');
                type_innate = "%Luyện đan%";
            } else if (type_craff == 'Trang bị') {
                $("#craff_title").text('Chế tạo trang bị');
                $("#craff_alt").text('Chọn trang bị muốn luyện');
                type_innate = "%Chế tạo trang bị%";
            } else if (type_craff == 'Minh văn') {
                $("#craff_title").text('Khắc minh văn');
                $("#craff_alt").text('Chọn trang bị minh văn');
                type_innate = "%Khắc minh văn%";
            } else if (type_craff == 'Trận pháp') {
                $("#craff_title").text('Chế tạo trận pháp');
                $("#craff_alt").text('Chọn trận pháp muốn chế tạo');
                type_innate = "%Chế tạo trận pháp%";
            }
            // Duyệt qua từng phần tử trong dữ liệu response và thêm thông tin của từng học viên vào chuỗi HTML
            for (var key in response) {
                var item = response[key];

                var medicineListHTML = '';

                medicineListHTML += '<div class="card" onClick="medicineCheck('+item.id+', `'+type_innate+'`)" >';
                medicineListHTML += '<img src="img/item/' + item.id + '.png" style="width:90%">';
                medicineListHTML += '<div style="width:100%;height:20px"><b>' + item.name + '</b></div>';
                medicineListHTML += '<div style="width:100%;height:20px">Yêu cầu thiên phú:'+item.required_innate+'</div>';
                medicineListHTML += '</div>';

                var shop = $(medicineListHTML);

                var item_required = $('<div class="cardMedicine">Vật liệu cần thiết:</div>');
                for (var key1 in item.item_craffs) {
                    var item_craff = item.item_craffs[key1];
                    if (!item_craff.user_item_quantity) item_craff.user_item_quantity  = 0;
                    item_required.append("<div><img src='img/item/"+ item_craff.item_craff_id +".png' >" 
                        + item_craff.item_craff_name + ":" + item_craff.user_item_quantity + "/" 
                        + item_craff.item_craff_quantity +"</div>");

                    shop.append(item_required);
                }
                $("#medicine_list").append(shop);
            }

            var popup = document.getElementById("medicinePopup");
            popup.style.display = "block";
        },
        error: function(xhr, status, error) {
            console.error("API error:", error);
        }
    });
}

var medicineInterval = null;
function medicineCheck(item_id, type_innate) {
    $("#craff_alt").text('');
    // Tạo biến để lưu chuỗi HTML của danh sách học viên
    var mapListHTML = '<div class="card" style="height:374px">';
    

    var type_craff = '';
    if (type_innate == '%Luyện đan%') {
        mapListHTML += '<p>Đang luyện đan...</p>';
        mapListHTML += '<img src="img/nauthuoc.gif" id="combatGif">';
        type_craff = 'Đan dược';
    } else if (type_innate == '%Chế tạo trang bị%') {
        mapListHTML += '<p>Đang chế tạo trang bị...</p>';
        type_craff = 'Trang bị';
        mapListHTML += '<img src="img/de.gif" id="combatGif">';
    } else if (type_innate == '%Khắc minh văn%') {
        type_craff = 'Minh văn';
        mapListHTML += '<p>Đang khắc minh văn...</p>';
        mapListHTML += '<img src="img/cove.gif" id="combatGif">';
    } else if (type_innate == '%Chế tạo trận pháp%') {
        type_craff = 'Trận pháp';
        mapListHTML += '<p>Đang chế tạo trận pháp...</p>';
        mapListHTML += '<img src="img/tranphap2.gif" id="combatGif">';
    }

    mapListHTML += '<h3 style="margin:4px 0; margin-top:105px">Danh sách vật phẩm thu thập:</h3>';
    mapListHTML += '<div id="item_collect" style="height:190px"></div>';
    mapListHTML += '</div>';

    document.getElementById('medicine_list').innerHTML = mapListHTML;

    medicineInterval = setInterval(function(){
        $.ajax({
            type: "POST",
            url: "api/item.php", // Đường dẫn tới file xử lý API
            data: JSON.stringify({
                user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
                type: 'medicine_check', 
                item_id:item_id,
                type_innate:type_innate
            }),
            contentType: "application/json",
            success: function(response) {
                var response = JSON.parse(response);

                if (response.msg) {
                    var popup = document.getElementById("msgPopup");
                    $("#msg").text(response.msg);
                    popup.style.display = "block";
                    showMedicinePopup(type_craff);
                    clearInterval(medicineInterval);
                } else {
                    // Tạo một đối tượng Date, đại diện cho thời gian hiện tại
                    var currentDate = new Date();

                    // Lấy thông tin về ngày, tháng, năm, giờ, phút, giây
                    var day = currentDate.getDate();
                    var month = currentDate.getMonth() + 1; // Lưu ý: Tháng bắt đầu từ 0
                    var year = currentDate.getFullYear();
                    var hours = currentDate.getHours();
                    var minutes = currentDate.getMinutes();
                    var seconds = currentDate.getSeconds();

                    if (seconds < 10) {
                        seconds = '0' + seconds;
                    }
                    // Định dạng thời gian để hiển thị
                    var formattedDate = day + '/' + month + '/' + year;
                    var formattedTime = hours + ':' + minutes + ':' + seconds;

                    // Hiển thị thời gian và ngày
                    var itemCollect = "<div>"+ formattedTime
                        + ": Bạn thu thập được <img src='img/item/" + response.id + ".png'  class='item-img'>" 
                        + response.name + "</div>";
                    $("#item_collect").prepend(itemCollect);
                }
            },
            error: function(xhr, status, error) {
                console.error("API error:", error);
            }
        });
    }, 5000);
}

function closeMedicinePopup() {
    var popup = document.getElementById("medicinePopup");
    popup.style.display = "none";
    clearInterval(medicineInterval);
}

function showMarketPopup(){
    $("#marketPopup").css('display', 'block');
    var typeMarket = $("#market_list").attr('data-type');
    if (typeMarket == 'sell') {
        $("#showSellMarketBtn").css('background', '#8ebae6');
        $("#showBuyMarketBtn").css('background', '#fff');

        $.ajax({
            type: "POST",
            url: "api/transaction.php", // Đường dẫn tới file xử lý API
            data: JSON.stringify({
                user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
                type: 'get_sell_market', 
            }),
            contentType: "application/json",
            success: function(response) {
                var response = JSON.parse(response);

                $("#market_list").text('');
                for (var key in response) {
                    var item = response[key];

                    var card = $("<div class='card'></div>");
                    var html = "<img src='img/item/"+item.id+".png'/>";
                    html+= "<div style='height:40px'><b>"+item.name+"</b></div>";
                    html+="<p>Số lượng đang bán:"+item.quantity+"</p>";
                    html+="<p>Tổng giá:"+(item.ton_value/1000000000)+ '<img src="img/ton.png" class="symbol" style="width: 18px;margin-bottom: -4px;">' + "</p>";
                    html+="<button onclick='removeItemMarketPlace("+item.market_place_id+")' style='margin:5px 0px'>Gỡ</button>";
                    card.append(html);
                    $("#market_list").append(card);
                }
            }
        });
    } else {
        $("#showBuyMarketBtn").css('background', '#8ebae6');
        $("#showSellMarketBtn").css('background', '#fff');
    }
}

function removeItemMarketPlace(market_place_id) {
    $.ajax({
        type: "POST",
        url: "api/transaction.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'remove_market_place', 
            market_place_id:market_place_id
        }),
        contentType: "application/json",
        success: function(response) {
            $("#msg").text('Gỡ thành công!');
            $("#msgPopup").css('display', 'block');
            showMarketPopup();
        }
    });
}

function sellMarket(user_item_id) {
    $.ajax({
        type: "POST",
        url: "api/transaction.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'check_wallet', 
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);
            if (response.msg) {
                $("#msgPopup").css('display', 'block');
                $("#msg").text(response.msg);
                $('#msg').append("<button onclick='connectToWallet()'>Kết nối</button>")
            } else {
                $.ajax({
                    type: "POST",
                    url: "api/item.php", // Đường dẫn tới file xử lý API
                    data: JSON.stringify({
                        user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
                        type: 'get_user_item', // Loại nguyên thạch (có thể thay đổi tùy theo yêu cầu của bạn)
                        user_item_id:user_item_id
                    }),
                    contentType: "application/json",
                    success: function(response) {
                        var item = JSON.parse(response);

                        var popup = document.getElementById("sellPopup");
                        popup.style.display = "block";

                        $("#sellPopupTitle").text('Bán trên Market Place');

                        $("#sell_item").text('');
                        var sellImg = '';

                        sellImg += '<div style="float;left;width:50%">';
                        sellImg += '<img src="img/item/' + item.item_id + '.png" style="width:100%">';
                        sellImg += '</div>';

                        var img = $(sellImg);
                        $("#sell_item").append(img);

                        var rightDiv = $("<div style='float:left;width:45%'></div>");
                        rightDiv.append("<b>"+item.item_name+"</b>");
                        var inputQuantity = $('<input id="quantitySell" type="number" min="1" value="1"/>');
                        inputQuantity.attr('max', item.quantity);
                        rightDiv.append(inputQuantity);



                        rightDiv.append('<span> / ' + item.quantity + '</span>');

                        var priceSell = $("<p>Nhập giá:<input id='priceSell' min='0.001' type='number'/></p>");
                        rightDiv.append(priceSell);

                        var total = $("<p>Tổng:</p>");
                        rightDiv.append(total);
                        

                        var btnSell = $("<button onclick='sellMarketSubmit("+user_item_id+")'>Bán</button>");
                        rightDiv.append(btnSell);
                        $("#sell_item").append(rightDiv);

                        $("#priceSell").change(function() {
                            var total1 = $("#priceSell").val() * $("#quantitySell").val();
                            $(total).text("Tổng: " + total1);
                            $(total).append("<img src='img/ton.png' class='symbol'/>");
                        });

                        $("#quantitySell").change(function() {
                            var total1 = $("#priceSell").val() * $("#quantitySell").val();
                            $(total).text("Tổng: " + total1);
                            $(total).append("<img src='img/ton.png' class='symbol'/>");
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("API error:", error);
                    }
                });
            }
        }
    });
}

function sellMarketSubmit(user_item_id) {
    var quantity = $("#quantitySell").val();
    var price_ton = $("#priceSell").val();
    $.ajax({
        type: "POST",
        url: "api/transaction.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'sell_market',
            user_item_id: user_item_id,
            quantity:quantity,
            price_ton:price_ton
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            if (response.msg) {
                $("#msgPopup").css('display', 'block');
                $("#msg").text("");
                $("#msg").append("<div>" + response.msg + "</div>");
            } else {
                $("#msgPopup").css('display', 'block');
                $("#msg").text("Đã đăng lên sàn thành công!");

                var popup = document.getElementById("sellPopup");
                popup.style.display = "none";
                showMarketPopup();
            }
        }
    });
}

function showListMarket(type, search = ''){
    if (type == 'sell') {
        $("#showSellMarketBtn").css('background', '#8ebae6');
        $("#showBuyMarketBtn").css('background', '#fff');

        showMarketPopup();
    } else {
        $("#showBuyMarketBtn").css('background', '#8ebae6');
        $("#showSellMarketBtn").css('background', '#fff');

        search = $("#input_search_buy_market").val();

        $.ajax({
            type: "POST",
            url: "api/transaction.php", // Đường dẫn tới file xử lý API
            data: JSON.stringify({
                user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
                type: 'get_buy_market',
                search: search
            }),
            contentType: "application/json",
            success: function(response) {
                var response = JSON.parse(response);

                $("#market_list").text('');
                $("#market_list").append("<input id='input_search_buy_market' placeholder='Nhập tên item cần tìm'/>");
                $("#market_list").append("<button onclick='showListMarket(`buy`)'>Tìm kiếm</button>");
                for (var key in response) {
                    var item = response[key];

                    var card = $("<div class='card'></div>");
                    var html = "<img src='img/item/"+item.id+".png'/>";
                    html+= "<div style='height:40px'><b>"+item.name+"</b></div>";
                    html+="<p>Số lượng đang bán:"+item.quantity+"</p>";
                    html+="<p>Tổng giá:"+(item.ton_value/1000000000)+ '<img src="img/ton.png" class="symbol" style="width: 18px;margin-bottom: -4px;">' + "</p>";
                    html+="<button onclick='buyMarketPlace("+item.market_place_id+")' style='margin:5px 0px'>Mua</button>";
                    card.append(html);
                    $("#market_list").append(card);
                }
            }
        });
    }
}

async function buyMarketPlace(market_place_id) {
    $.ajax({
        type: "POST",
        url: "api/transaction.php", // Đường dẫn tới file xử lý API
        data: JSON.stringify({
            user_id: tele_id, // Thay đổi user_id thành giá trị thực tế
            type: 'buy_market_get_wallet',
            market_place_id:market_place_id
        }),
        contentType: "application/json",
        success: function(response) {
            var response = JSON.parse(response);

            var sellAddress = response.wallet;
            var amount = response.ton_value;

            buyMarketPlaceCommit(sellAddress, market_place_id, amount);
        }
    });
}

async function buyMarketPlaceCommit(sellAddress, market_place_id, amount) {
    if (!tonConnectUI) {
        tonConnectUI = new TON_CONNECT_UI.TonConnectUI({
            manifestUrl: 'https://vayugo.000webhostapp.com/tonconnect-manifest.json',
            buttonRootId: 'ton-connect'
        });
    }

    console.log(connect);
    if (!connect) {
        const connectedWallet = await tonConnectUI.connectWallet();
        connect = true;
        address = connectedWallet.account.address;
        console.log("Connect");
    }


    var fee = amount * 0.1;

    const result = await tonConnectUI.sendTransaction({
      messages: [
        {
          address: "UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH",
          amount: BigInt(fee).toString(),
        },
        {
          address: sellAddress,
          amount: BigInt(amount - fee).toString(),
        }
      ],
      validUntil: Date.now() + 5 * 60 * 1000, // 5 minutes for user to approve
    });

    console.log(result);
    if (result) {
        $.ajax({
            type: "POST",
            url: "api/transaction.php",
            data: JSON.stringify({
                user_id: tele_id,
                type: 'check_transaction_market',
                market_place_id:market_place_id,
                address:address
            }),
            contentType: "application/json",
            success: function(response) {
                var response = JSON.parse(response);

                if (response.msg) {
                    $("#msgPopup").css('display', 'block');
                    $("#msg").text(response.msg);
                } else {
                    $("#msgPopup").css('display', 'block');
                    $("#msg").text("Mua thành công");
                    showListMarket('buy');
                }
            },
            error: function(xhr, status, error) {
                console.error("API error:", error);
            }
        });
    }
}