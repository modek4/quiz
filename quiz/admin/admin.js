$(document).ready(function() {
    function add_log(title, text, user, destination, data){
        $.ajax({
            type: 'POST',
            url: 'log.php',
            data: {title: title, text: text, user: user, destination: destination, data: data},
            error: function(xhr, status, error) {
                console.error(xhr, status, error);
            }
        });
    }
    var chart = undefined;
    $('.admin_icon').click(function() {
        $('.admin_menu').css('top', '0');
        $.ajax({
            type: 'POST',
            url: './admin/index.php',
            data: {navbar : 'main', content: 'main'},
            async: false,
            success: function(response) {
                $('.admin_menu_content').html(response);
                run();
                chart = undefined;
                data_main();
                if(main_status == false){
                    main();
                    main_status = true;
                }
            },
            error: function(xhr, status, error) {
                add_log("admin_menu", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                notifyshow(status+" ("+xhr.status+"): "+error, '');
            }
        });
    });
    var main_status = false;
    var report_status = false;
    var menage_status = false;
    var users_status = false;
    function run(){
        $('.admin_menu_close i').click(function() {
            $('.admin_menu').css('top', '-150%');
            if($('.reload_logs').length){
                $('.reload_logs').css('bottom', '-5em');
            }
        });
        var navbar = document.querySelector('.navbar');
        navbar.addEventListener('click', function(e) {
            if (e.target.classList.contains('active') || e.target.classList.contains('navbar')) {
                return;
            }
            navbar.querySelectorAll('.active').forEach(function(node) {
                node.classList.remove('active');
            });
            e.target.classList.add('active');
            switch (e.target.getAttribute('data-id')) {
                case 'main':
                    $.ajax({
                        type: 'POST',
                        url: './admin/index.php',
                        data: {navbar : 'main', content: 'main'},
                        success: function(response) {
                            $('.admin_menu_content').html(response);
                            run();
                            chart = undefined;
                            data_main();
                            if(main_status == false){
                                main();
                                main_status = true;
                            }
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: main", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                    break;
                case 'report':
                    $.ajax({
                        type: 'POST',
                        url: './admin/index.php',
                        data: {navbar : 'report', content: 'report'},
                        success: function(response) {
                            $('.admin_menu_content').html(response);
                            run();
                            if(report_status == false){
                                report();
                                report_status = true;
                            }
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: report", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                    break;
                case 'menage':
                    $.ajax({
                        type: 'POST',
                        url: './admin/index.php',
                        data: {navbar : 'menage', content: 'menage'},
                        success: function(response) {
                            $('.admin_menu_content').html(response);
                            run();
                            if(menage_status == false){
                                menage();
                                menage_status = true;
                            }
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: menage", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                    break;
                case 'users':
                    $.ajax({
                        type: 'POST',
                        url: './admin/index.php',
                        data: {navbar : 'users', content: 'users'},
                        success: function(response) {
                            $('.admin_menu_content').html(response);
                            run();
                            if(users_status == false){
                                users();
                                users_status = true;
                            }
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: users", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                    break;
                case 'logs':
                    function logs(log = '0'){
                        $.ajax({
                            type: 'POST',
                            url: './admin/index.php',
                            data: {navbar : 'logs', content: 'logs', log: log},
                            success: function(response) {
                                $('.admin_menu_content').html(response);
                                run();
                                $('.logs_content_select').change(function(){
                                    let log = $(this).val();
                                    logs(log);
                                });
                            },
                            error: function(xhr, status, error) {
                                add_log("admin_menu: logs", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                                notifyshow(status+" ("+xhr.status+"): "+error, '');
                            }
                        });
                    }
                    logs();
                    $(document).on('click', '.reload_logs', function() {
                        $('.reload_logs').addClass('reload_active');
                        let log = $('.logs_content_select').val();
                        logs(log);
                    });
                    break;
                default:
                    break;
            }
        });
    }
    //main chart
    function main_chart(sort = "7", term = null){
        if ($('#main_analytic_chart').length > 0) {
            const ctx = document.getElementById('main_analytic_chart').getContext('2d');
            let delayed = false;
            if($('body').hasClass('dark-mode')){
                Chart.defaults.color = 'white';
                Chart.defaults.scale.grid.color = 'rgba(255, 255, 255, 0.1)';
            }else{
                Chart.defaults.color = 'black';
                Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.1)';
            }
            let animation = {
                onComplete: () => {
                  delayed = true;
                },
                delay: (context) => {
                  let delay = 0;
                  if (context.type === 'data' && context.mode === 'default' && !delayed) {
                    delay = context.dataIndex * 200 + context.datasetIndex * 100;
                  }
                  return delay;
                },
            };
            let options = {
                animation: animation,
                maintainAspectRatio: false,
                indexAxis: 'x',
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.raw;
                                let title = context.dataset.label;
                                return title + ": " + label;
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            };
            $.ajax({
                type: 'POST',
                url: './admin/db/analytic_chart.php',
                data: {term: term},
                success: function(response) {
                    let parsed_response_chart = JSON.parse(response);
                    if(sort != "all"){
                        var data_length = parseInt(sort);
                        if(data_length > parsed_response_chart.labels.length){
                            data_length = parsed_response_chart.labels.length;
                        }
                    }
                    function random_color(usedColors) {
                        if($('body').hasClass('dark-mode')){
                            var light = "65%";
                        }else{
                            var light = "45%";
                        }
                        let colors = [
                            "hsl(0, 100%, " + light + ")",
                            "hsl(20, 100%, " + light + ")",
                            "hsl(40, 100%, " + light + ")",
                            "hsl(80, 100%, " + light + ")",
                            "hsl(100, 100%, " + light + ")",
                            "hsl(120, 100%, " + light + ")",
                            "hsl(140, 100%, " + light + ")",
                            "hsl(160, 100%, " + light + ")",
                            "hsl(180, 100%, " + light + ")",
                            "hsl(200, 100%, " + light + ")",
                            "hsl(220, 100%, " + light + ")",
                            "hsl(240, 100%, " + light + ")",
                            "hsl(260, 100%, " + light + ")",
                            "hsl(280, 100%, " + light + ")",
                            "hsl(300, 100%, " + light + ")",
                            "hsl(320, 100%, " + light + ")",
                            "hsl(340, 100%, " + light + ")",
                        ];
                        let color;
                        do {
                            color = colors[Math.floor(Math.random() * colors.length)];
                        } while (usedColors.includes(color));
                        return color;
                    }
                    if(sort != "all"){
                        if(data_length < parsed_response_chart.labels.length){
                            parsed_response_chart.labels = parsed_response_chart.labels.slice(parsed_response_chart.labels.length - data_length);
                        }
                    }
                    let data = {
                        labels: parsed_response_chart.labels,
                        datasets: []
                    };
                    const usedColors = [];
                    for (let i = 0; i < parsed_response_chart.datas.label.length; i++) {
                        const backgroundColor = random_color(usedColors);
                        const borderColor = backgroundColor;
                        if(sort != "all"){
                            parsed_response_chart.datas.data[i] = parsed_response_chart.datas.data[i].slice(parsed_response_chart.datas.data[i].length - data_length);
                        }
                        const dataset = {
                            label: parsed_response_chart.datas.label[i],
                            data: parsed_response_chart.datas.data[i],
                            backgroundColor,
                            borderColor,
                            borderWidth: 2,
                            pointRadius: 2,
                            hoverOffset: 4,
                            tension: 0.2
                        };
                        data.datasets.push(dataset);
                        usedColors.push(backgroundColor);
                    }
                    let config = {
                        type: 'bar',
                        data: data,
                        options: options,
                    };
                    if(chart == undefined){
                        chart = new Chart(ctx, config);
                    }else{
                        chart.data = data;
                        chart.update();
                    }
                    $(window).resize(function() {
                        if($(window).width() < 768){
                            Chart.defaults.font.size = ($(window).width()/100) + 5;
                        }else{
                            Chart.defaults.font.size = ($(window).width()/100) + 2;
                        }
                    });
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: main chart", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        }
    }
    function data_main(){
        //load right side data
        $.ajax({
            type: 'POST',
            url: './admin/db/main_data.php',
            success: function(response) {
                $('.main_right').html(response);
                //load subjects table
                $.ajax({
                    type: 'POST',
                    url: './admin/db/main_subjects.php',
                    success: function(response) {
                        $('.main_left_subjects_content').html(response);
                        main_chart();
                        //data animation
                        $('.users_all, .users_online, .users_offline, .questions_all, .subjects_all, .reports_all, .views_table, .questions_table').each(function() {
                            let $this = $(this), count_to = $(this).html();
                            let count_speed = 0;
                            if(count_to < 500) {
                                count_speed = 1000;
                            } else {
                                count_speed = 5000;
                            }
                            $({ count_num: 0}).animate({
                                count_num: count_to
                            },
                            {
                                duration: count_speed,
                                easing:'swing',
                                step: function() {
                                    $this.text(Math.floor(this.count_num));
                                },
                                complete: function() {
                                    $this.text(this.count_num);
                                }
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: main subjects", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            },
            error: function(xhr, status, error) {
                add_log("admin_menu: main data", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                notifyshow(status+" ("+xhr.status+"): "+error, '');
            }
        });
    }
    function main(){
        //show logs
        $(document).on('change', '.select_logs', function() {
            let value = $(this).val();
            value != '' ? window.open('../quiz/logs/'+value+'.json', '_blank') : '';
        });
        //change chart sort
        $(document).on('change', '.select_latest_data', function() {
            let sort = $(this).val();
            let term = $('.select_latest_term').val();
            main_chart(sort, term);
        });
        //change chart term
        $(document).on('change', '.select_latest_term', function() {
            let term = $(this).val();
            let sort = $('.select_latest_data').val();
            main_chart(sort, term);
        });
    }
    function report(){
        //auto change textarea height
        function adjust_textarea_height(textarea) {
            textarea.style.height = 'auto';
            let contentHeight = textarea.scrollHeight;
            textarea.style.height = contentHeight + 'px';
        }
        document.addEventListener('input', function(event) {
            if (event.target && event.target.nodeName === 'TEXTAREA') {
                adjust_textarea_height(event.target);
            }
        });
        document.querySelectorAll('textarea').forEach(function(textarea) {
            adjust_textarea_height(textarea);
            textarea.addEventListener('focus', function() {
                adjust_textarea_height(textarea);
            });
        });
        $(window).resize(function() {
            document.querySelectorAll('textarea').forEach(function(textarea) {
                adjust_textarea_height(textarea);
            });
        });
        //reload report
        $(document).on('click', '.fa-rotate-right', function() {
            $(this).addClass('active');
            let id = $(this).attr('data-id');
            let subject = $(this).attr('data-name');
            $.ajax({
                type: 'POST',
                url: 'admin/db/reload_report_question.php',
                data: { subject: subject, id: id },
                success: function(response) {
                    $('#report_item-' + id).html(response);
                    document.querySelectorAll('textarea').forEach(function(textarea) {
                        adjust_textarea_height(textarea);
                    });
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: report reload", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //copy report
        $(document).on('click', '.fa-copy', function() {
            let question = $(this).parent().find('textarea').html();
            let letter = 97;
            $(this).parent().find('.answer textarea').each(function(index, element) {
                question += "\n" + String.fromCharCode(letter) + ") " + $(element).val();
                letter++;
            });
            let temp = $("<textarea>");
            $("body").append(temp);
            temp.val(question).select();
            if(document.execCommand("copy")){
                notifyshow(lang_text['admin']['reports']['copy']['success'], '');
            }else{
                notifyshow(lang_text['admin']['reports']['copy']['error'], '');
            }
            temp.remove();
        });
        //change correct answer
        $(document).on('click', '.answer span', function() {
            let parent = $(this).parent();
            if($(this).hasClass('active')){
                $(this).removeClass('active');
                if(parent.find('textarea').hasClass('correct_user_answer')){
                    parent.find('textarea').removeClass('correct_user_answer');
                    parent.find('textarea').addClass('user_answer');
                } else if(parent.find('textarea').hasClass('correct_answer')){
                    parent.find('textarea').removeClass('correct_answer');
                    parent.find('textarea').addClass('no_checked');
                }
            }else{
                $(this).addClass('active');
                if(parent.find('textarea').hasClass('user_answer')){
                    parent.find('textarea').removeClass('user_answer');
                    parent.find('textarea').addClass('correct_user_answer');
                } else if(parent.find('textarea').hasClass('no_checked')){
                    parent.find('textarea').removeClass('no_checked');
                    parent.find('textarea').addClass('correct_answer');
                }
            }
        });
        //delete answer
        $(document).on('click', '.answer i', function() {
            $(this).parent().remove();
        });
        //add answer
        $(document).on('click', '.fa-plus', function() {
            $(this).parent().find('.answer:last').after('<div class="answer"><span>•</span><textarea class="no_checked"></textarea><i class="fas fa-trash-alt"></i></div>');
        });
        //update question
        $(document).on('click', '.report_content_item_update', function() {;
            let subject = $(this).parent().parent().find('h4');
            let lastDashIndex = subject.text().lastIndexOf(" - ");
            let question_id = subject.text().substring(lastDashIndex + 3);
            subject = subject.text().substring(0, lastDashIndex);
            let question = $(this).parent().parent().find('h5 textarea').val();
            let answers = [];
            let correct_answers = [];
            $(this).parent().parent().find('.answers').children().each(function(index, element) {
                if($(element).find('span').hasClass('active')){
                    correct_answers.push(String.fromCharCode(97 + index));
                }
                answers.push($(element).find('textarea').val());
            });
            answers = answers.join("♥");
            correct_answers = correct_answers.join(";");
            correct_answers += ";";
            $.ajax({
                type: 'POST',
                url: 'admin/db/update_report_question.php',
                data: { subject: subject, question_id: question_id, question: question, answers: answers, correct_answers: correct_answers },
                success: function(response) {
                    $.ajax({
                        type: 'POST',
                        url: './admin/index.php',
                        data: {navbar : 'report', content: 'report'},
                        success: function(response) {
                            $('.admin_menu_content').html(response);
                            run();
                            if(report_status == false){
                                report();
                                report_status = true;
                            }
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: report reload after update question", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: report update question", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //decline report
        $(document).on('click', '.report_content_item_decline', function() {
            let subject = $(this).parent().parent().find('h4');
            let lastDashIndex = subject.text().lastIndexOf(" - ");
            let question_id = subject.text().substring(lastDashIndex + 3);
            subject = subject.text().substring(0, lastDashIndex);
            $.ajax({
                type: 'POST',
                url: 'admin/db/decline_report_question.php',
                data: { subject: subject, question_id: question_id },
                success: function(response) {
                    $.ajax({
                        type: 'POST',
                        url: './admin/index.php',
                        data: {navbar : 'report', content: 'report'},
                        success: function(response) {
                            $('.admin_menu_content').html(response);
                            run();
                            if(report_status == false){
                                report();
                                report_status = true;
                            }
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: report reload after decline question", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: report decline question", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //remove question
        $(document).on('click', '.report_content_item_remove', function() {
            let subject = $(this).parent().parent().find('h4');
            let lastDashIndex = subject.text().lastIndexOf(" - ");
            let question_id = subject.text().substring(lastDashIndex + 3);
            subject = subject.text().substring(0, lastDashIndex);
            let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
            '<h3>'+lang_text['admin']['reports']['remove']['confirm']+'</h3>'+
            '<div class="confirm_window_buttons">'+
                '<button class="confirm_window_button_yes">'+lang_text['admin']['reports']['remove']['confirm_yes']+'</button>'+
                '<button class="confirm_window_button_no">'+lang_text['admin']['reports']['remove']['confirm_no']+'</button>'+
            '</div></div></div>';
            $('body').append(confirm_window);
            $('.confirm_window').css("display", "flex").hide().fadeIn(300);
            $('.confirm_window_button_no').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                }, 300);
            });
            $('.confirm_window_button_yes').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                    $.ajax({
                        type: 'POST',
                        url: 'admin/db/remove_report_question.php',
                        data: { subject: subject, question_id: question_id },
                        success: function(response) {
                            $.ajax({
                                type: 'POST',
                                url: './admin/index.php',
                                data: {navbar : 'report', content: 'report'},
                                success: function(response) {
                                    $('.admin_menu_content').html(response);
                                    run();
                                    if(report_status == false){
                                        report();
                                        report_status = true;
                                    }
                                },
                                error: function(xhr, status, error) {
                                    add_log("admin_menu: report reload after remove question", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                                }
                            });
                            notifyshow(response, '');
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: report remove queston", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                }, 300);
            });
        });
    }
    function menage(){
        //slide menu form
        function move_form(form, direction) {
            if(direction == "right"){
                var dataNumber = parseInt(form.attr('data-number'));
                var parent = form.parent().parent();
                parent.find('[data-number="' + (dataNumber - 1) + '"]').removeClass('left_show');
                parent.find('[data-number="' + dataNumber + '"]').addClass('left_show');
            }else if(direction == "left"){
                var dataNumber = parseInt(form.attr('data-number'));
                var parent = form.parent().parent();
                parent.find('[data-number="' + (dataNumber + 1) + '"]').removeClass('left_show');
                parent.find('[data-number="' + dataNumber + '"]').addClass('left_show');
            }
        }
        //check if file is txt
        $(document).on('change', '.menage_main_add_content_item input[type="file"], .menage_main_add_more_content_item input[type="file"]', function() {
            var fileName = $(this).val().split('\\').pop();
            var allowed_extensions = ['txt'];
            if(allowed_extensions.indexOf(fileName.split('.').pop()) == -1){
                $(this).val('');
                notifyshow(lang_text['admin']['menage']['quiz_add']['file_extension'], '');
            }
            var newText = fileName ? lang_text['admin']['menage']['quiz_add']['file_placeholder_success'] : lang_text['admin']['menage']['quiz_add']['file_placeholder'];
            $(this).attr('data-text', newText);
        });
        //add subject
        $(document).on('click', '.menage_main_add_content_item button.next, .menage_main_add_content_item button.prev, .menage_main_add_content_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            move_form($(this), direction)
        });
        $('#quiz_add_form').submit(function(event) {
            event.preventDefault();
            var form_data_add = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_add.php',
                data: form_data_add,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_add_content_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_add_content_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_add']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //add more questions
        $(document).on('click', '.menage_main_add_more_content_item button.next, .menage_main_add_more_content_item button.prev, .menage_main_add_more_content_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 1){
                $.ajax({
                    type: 'POST',
                    url: './admin/db/subjects_list.php',
                    success: function(response) {
                        $('.subject_list_add_more').html(response);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load subjects list for add more questions", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            move_form($(this), direction);
        });
        $('#quiz_add_more_form').submit(function(event) {
            event.preventDefault();
            var form_data_add_more = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_add.php',
                data: form_data_add_more,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_add_more_content_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_add_more_content_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_add_more']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //change quiz status
        $(document).on('click', '.menage_main_status_content_item button.next, .menage_main_status_content_item button.prev, .menage_main_status_content_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 2){
                let share_val = $(this).parent().parent().find('select[name="share"]').val();
                $.ajax({
                    type: 'POST',
                    url: './admin/db/subjects_list.php',
                    data: {share: share_val},
                    success: function(response) {
                        $('.subject_list_status').html(response);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load subjects list for change status", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            move_form($(this), direction);
        });
        $('#quiz_main_status_form').submit(function(event) {
            event.preventDefault();
            var quiz_main_status_form = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_status.php',
                data: quiz_main_status_form,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_status_content_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_status_content_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_status']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //quiz download
        $(document).on('click', '.menage_main_download_content_item button.next, .menage_main_download_content_item button.prev, .menage_main_download_content_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 1){
                $.ajax({
                    type: 'POST',
                    url: './admin/db/subjects_list.php',
                    success: function(response) {
                        $('.subject_list_download').html(response);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load subjects list for download quiz", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            if($(this).parent().parent().find('select[name="format"]').val() == "pdf" || $(this).parent().parent().find('select[name="format"]').val() == "json"){
                $(this).parent().parent().find('input[name="separator"]').attr('disabled', 'disabled');
                $(this).parent().parent().find('input[name="separator"]').attr('placeholder', lang_text['admin']['menage']['quiz_download']['separator_placeholder_disabled']);
                $(this).parent().parent().find('input[name="separator"]').val('');
            }else if($(this).parent().parent().find('select[name="signed_type"]').val() == "onlyCorrect" || $(this).parent().parent().find('select[name="signed_type"]').val() == "allWithout"){
                $(this).parent().parent().find('input[name="separator"]').attr('disabled', 'disabled');
                $(this).parent().parent().find('input[name="separator"]').attr('placeholder', lang_text['admin']['menage']['quiz_download']['separator_placeholder_not_needed']);
                $(this).parent().parent().find('input[name="separator"]').val('');
            }else{
                $(this).parent().parent().find('input[name="separator"]').removeAttr('disabled');
                $(this).parent().parent().find('input[name="separator"]').attr('placeholder', lang_text['admin']['menage']['quiz_download']['separator_placeholder']);
            }
            move_form($(this), direction)
        });
        $('#quiz_main_download_form').submit(function(event) {
            event.preventDefault();
            var form_data_add_more = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_download.php',
                data: form_data_add_more,
                processData: false,
                contentType: false,
                success: function(response) {
                    let parsed_response = JSON.parse(response);
                    let response_data = parsed_response.data;
                    let response_message = parsed_response.message;
                    if(response_message == "" && response_data != ""){
                        let data2 = './admin/files/'+response_data
                        downloadFile(data2, response_data);
                    }else{
                        notifyshow(response_message, '');
                    }
                    $('.menage_main_download_content_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_download_content_item:first').addClass('left_show');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_download']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        function downloadFile(url, file_name) {
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'blob';
            xhr.onload = function() {
                var blob = new Blob([xhr.response], {type: 'application/octet-stream'});
                var url = URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = file_name;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                $.post('./admin/db/quiz_download.php', {delfile: file_name}, function(response) {
                    let parsed_response = JSON.parse(response);
                    let response_message = parsed_response.message;
                    if(response_message != ""){
                        notifyshow(response_message, '');
                    }
                });
            };
            xhr.open('GET', url);
            xhr.send();
        }
        //quiz delete
        $(document).on('click', '.menage_main_delete_content_item button.next, .menage_main_delete_content_item button.prev, .menage_main_delete_content_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 1){
                let share_val = 0;
                $.ajax({
                    type: 'POST',
                    url: './admin/db/subjects_list.php',
                    data: {share: share_val},
                    success: function(response) {
                        $('.subject_list_delete').html(response);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load subjects list for delete quiz", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            move_form($(this), direction);
        });
        $('#quiz_main_delete_form').submit(function(event) {
            event.preventDefault();
            var quiz_main_delete_form = new FormData(this);
            let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
            '<h3>'+lang_text['admin']['menage']['quiz_delete']['confirm']+'</h3>'+
            '<div class="confirm_window_buttons">'+
                '<button class="confirm_window_button_yes">'+lang_text['admin']['menage']['quiz_delete']['confirm_yes']+'</button>'+
                '<button class="confirm_window_button_no">'+lang_text['admin']['menage']['quiz_delete']['confirm_no']+'</button>'+
            '</div></div></div>';
            $('body').append(confirm_window);
            $('.confirm_window').css("display", "flex").hide().fadeIn(300);
            $('.confirm_window_button_no').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                }, 300);
            });
            $('.confirm_window_button_yes').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                    $.ajax({
                        type: 'POST',
                        url: './admin/db/quiz_delete.php',
                        data: quiz_main_delete_form,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('.menage_main_delete_content_item').each(function(index, element) {
                                $(element).removeClass('left_show');
                            });
                            $('.menage_main_delete_content_item:first').addClass('left_show');
                            notifyshow(response, '');
                        },
                        error: function(xhr, status, error) {
                            add_log(lang_text['admin']['menage']['quiz_delete']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                }, 300);
            });
        });
        //rename quiz
        $(document).on('click', '.menage_main_rename_content_item button.next, .menage_main_rename_content_item button.prev, .menage_main_rename_content_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 1){
                $.ajax({
                    type: 'POST',
                    url: './admin/db/subjects_list.php',
                    success: function(response) {
                        $('.subject_list_rename').html(response);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load subjects list for rename quiz", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            move_form($(this), direction);
        });
        $('#quiz_main_rename_form').submit(function(event) {
            event.preventDefault();
            var quiz_main_rename_form = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_rename.php',
                data: quiz_main_rename_form,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_rename_content_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_rename_content_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_rename']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //add/remove mods
        $(document).on('click', '.menage_main_moderation_content_item button.next, .menage_main_moderation_content_item button.prev, .menage_main_moderation_content_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 2){
                let mod = $(this).parent().parent().find('select[name="add_remove"]').val();
                $.ajax({
                    type: 'POST',
                    url: './admin/db/users_list.php',
                    data: {mod: mod},
                    success: function(response) {
                        $('.users_list_moderation').html(response);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load users list for add/remove mod", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            move_form($(this), direction);
        });
        $('#quiz_main_moderation_form').submit(function(event) {
            event.preventDefault();
            var quiz_main_moderation_form = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_mod.php',
                data: quiz_main_moderation_form,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_moderation_content_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_moderation_content_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_moderation']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //code remove
        $(document).on('click', '.menage_main_code_content_left_item button.next, .menage_main_code_content_left_item button.prev, .menage_main_code_content_left_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 1){
                $.ajax({
                    type: 'POST',
                    url: './admin/db/codes_list.php',
                    success: function(response) {
                        $('.code_list').html(response);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load codes list for remove", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            move_form($(this), direction);
        });
        $('#quiz_main_code_remove_form').submit(function(event) {
            event.preventDefault();
            var quiz_main_code_remove_form = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_code.php',
                data: quiz_main_code_remove_form,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_code_content_left_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_code_content_left_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_code']['remove']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //code add
        $(document).on('click', '.menage_main_code_content_right_item button.next, .menage_main_code_content_right_item button.prev, .menage_main_code_content_right_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            move_form($(this), direction);
        });
        $('#quiz_main_code_add_form').submit(function(event) {
            event.preventDefault();
            var quiz_main_code_add_form = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_code.php',
                data: quiz_main_code_add_form,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_code_content_right_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_code_content_right_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_code']['add']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //change points analytic
        $(document).on('click', '.menage_main_analytic_content_right_item button.next, .menage_main_analytic_content_right_item button.prev, .menage_main_analytic_content_right_item button.send', function() {
            let direction = $(this).hasClass('next') ? "right" : "left";
            if($(this).attr('data-number') == 1){
                $.ajax({
                    type: 'POST',
                    url: './admin/db/quiz_analytic.php',
                    success: function(response) {
                        response = JSON.parse(response);
                        $('.menage_main_analytic_content_right_item[data-number="1"] input[name="correct"]').val(response[0]);
                        $('.menage_main_analytic_content_right_item[data-number="2"] input[name="incorrect"]').val(response[1]);
                        $('.menage_main_analytic_content_right_item[data-number="3"] input[name="halfcorrect"]').val(response[2]);
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: menage load analytic points for change value", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
            move_form($(this), direction);
        });
        $('#quiz_main_analytic_form').submit(function(event) {
            event.preventDefault();
            var quiz_main_analytic_form = new FormData(this);
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_analytic.php',
                data: quiz_main_analytic_form,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.menage_main_analytic_content_right_item').each(function(index, element) {
                        $(element).removeClass('left_show');
                    });
                    $('.menage_main_analytic_content_right_item:first').addClass('left_show');
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log(lang_text['admin']['menage']['quiz_analytic']['title'], "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //test points value
        $(document).on('click', '.menage_main_analytic_content_left_random button', function() {
            let correct_points = $('.menage_main_analytic_content_right_item[data-number="1"] input[name="correct"]').val();
            let incorrect_points = $('.menage_main_analytic_content_right_item[data-number="2"] input[name="incorrect"]').val();
            let halfcorrect_points = $('.menage_main_analytic_content_right_item[data-number="3"] input[name="halfcorrect"]').val();
            let random_points = $('.analytic_random_showcase').html();
            $.ajax({
                type: 'POST',
                url: './admin/db/quiz_analytic.php',
                data: {correct_points: correct_points, incorrect_points: incorrect_points, halfcorrect_points: halfcorrect_points, random_points: random_points},
                success: function(response) {
                    response = JSON.parse(response);
                    if(response[6].split(" ") == random_points.split(" ")){
                        $('.menage_main_analytic_content_left_random button').click();
                    }else{
                        $('.analytic_showcase_correct').html(response[0]);
                        $('.analytic_showcase_incorrect').html(response[1]);
                        $('.analytic_showcase_halfcorrect').html(response[2]);
                        $('.analytic_showcase_checked').html(response[3]);
                        $('.analytic_showcase_maxchecked').html(response[4]);
                        $('.analytic_showcase_count').html(response[5]);
                        $('.analytic_random_showcase').html(response[6]);
                    }
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: menage load random analytic value", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
    }
    function users(){
        //search users
        $(document).on('keyup', '#search_user', function() {
            let search = $(this).val();
            $('.users_list_content_item').each(function(index, element) {
                if($(element).find('span').text().toLowerCase().indexOf(search.toLowerCase()) == -1){
                    $(element).hide();
                }else{
                    $(element).show();
                }
            });
        });
        //send notification to user
        $(document).on('click', '.fa-paper-plane', function() {
            var user_email = $(this).attr('data-id');
            if(user_email != "" && user_email != undefined){
                let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
                '<h3>'+lang_text['admin']['users']['notification']['user']['confirm']+'</h3>'+
                '<input type="text" class="notification_all_input_title" placeholder="'+lang_text['admin']['users']['notification']['user']['title_box']+'">'+
                '<input type="text" class="notification_all_input_text" placeholder="'+lang_text['admin']['users']['notification']['user']['text_box']+'">'+
                '<div class="confirm_window_buttons">'+
                    '<button class="confirm_window_button_yes disabled" disabled="disabled">'+lang_text['admin']['users']['notification']['user']['confirm_yes']+'</button>'+
                    '<button class="confirm_window_button_no">'+lang_text['admin']['users']['notification']['user']['confirm_no']+'</button>'+
                '</div></div></div>';
                $('body').append(confirm_window);
                $('.confirm_window').css("display", "flex").hide().fadeIn(300);
                $('.confirm_window_button_no').click(function() {
                    $('.confirm_window').fadeOut(300);
                    setTimeout(function() {
                        $('.confirm_window').remove();
                    }, 300);
                });
                $('.notification_all_input_title, .notification_all_input_text').keyup(function() {
                    if($('.notification_all_input_title').val() != "" && $('.notification_all_input_text').val() != ""){
                        $('.confirm_window_button_yes').removeClass('disabled');
                        $('.confirm_window_button_yes').removeAttr('disabled');
                    }else{
                        $('.confirm_window_button_yes').addClass('disabled');
                        $('.confirm_window_button_yes').attr('disabled', 'disabled');
                    }
                });
                $('.confirm_window_button_yes').click(function() {
                    $('.confirm_window').fadeOut(300);
                    setTimeout(function() {
                        let notification_title = $('.notification_all_input_title').val();
                        let notification_text = $('.notification_all_input_text').val();
                        $('.confirm_window').remove();
                        console.log(notification_title, notification_text, user_email);
                        $.ajax({
                            type: 'POST',
                            url: './admin/db/send_notification.php',
                            data: {title: notification_title, text: notification_text, email: user_email},
                            success: function(response) {
                                notifyshow(response, '');
                            },
                            error: function(xhr, status, error) {
                                add_log("admin_menu: users send notification to user", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                                notifyshow(status+" ("+xhr.status+"): "+error, '');
                            }
                        });
                    }, 300);
                });
            }
        });
        //user menu
        $(document).on('click', '.fa-ellipsis-vertical', function() {
            var user_code = $(this).attr('data-id');
            if(user_code != "" && user_code != undefined){
                $.ajax({
                    type: 'POST',
                    url: './admin/db/user_settings.php',
                    data: {user_code: user_code},
                    success: function(response) {
                        $('.users_user_settings_content').html(response);
                        let devices_count = $('.users_user_settings_content .device').length;
                        $('.device_count').html(devices_count);
                        let address_count = $('.users_user_settings_content .addresses li').length;
                        $('.address_count').html(address_count);
                        $('.users_user_settings').addClass('active');
                        $(document).on('click', '.fa-chevron-left', function() {
                            $('.users_user_settings').removeClass('active');
                        });
                        show_chart();
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: users load user settings", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
        });
        $(document).on('click', '.fa-chevron-left', function() {
            $(this).parent().removeClass('active');
        });
        //edit term
        $(document).on('click', '.change_user_term_access', function() {
            let term_val = $(this).attr("data-term");
            let user_code = $(this).attr("data-code");
            let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
                '<h3>'+lang_text['admin']['users']['term']['change_title']+'</h3>'+
                '<input type="text" placeholder="'+lang_text['admin']['users']['term']['actual_term']+': '+term_val+'" disabled="disabled">'+
                '<input type="text" class="term_input_text" placeholder="'+lang_text['admin']['users']['term']['new_term']+'" value="'+term_val+'">'+
                '<div class="confirm_window_buttons">'+
                    '<button class="confirm_window_button_yes">'+lang_text['admin']['users']['term']['confirm_yes']+'</button>'+
                    '<button class="confirm_window_button_no">'+lang_text['admin']['users']['term']['confirm_no']+'</button>'+
                '</div></div></div>';
            $('body').append(confirm_window);
            $('.confirm_window').css("display", "flex").hide().fadeIn(300);
            $('.confirm_window_button_no').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                }, 300);
            });
            $('.confirm_window_button_yes').click(function() {
                let term_val = $('.term_input_text').val();
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                    $.ajax({
                        type: 'POST',
                        url: './admin/db/edit_term.php',
                        data: {term: term_val, code: user_code},
                        success: function(response) {
                            notifyshow(response, '');
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: users change user term", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                }, 300);
            });
        });
        //watch user
        $(document).on('click', '.watch_user', function() {
            let user_code = $(this).attr("data-code");
            watch_user(user_code);
        });
        $(document).on('click', '.reload_users_user', function() {
            $(this).children('i').addClass('fa-spin');
            let user_code = $(this).attr("data-code");
            watch_user(user_code);
        });
        function watch_user(user_code){
            $.ajax({
                type: 'POST',
                url: './admin/db/user_settings.php',
                data: {user_code: user_code, user_watch: 1},
                success: function(response) {
                    $('.users_user_settings_content').html(response);
                    $('.users_user_settings').addClass('active');
                    $(document).on('click', '.fa-chevron-left', function() {
                        $('.users_user_settings').removeClass('active');
                    });
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: users watch user quiz", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        }
        //block user
        $(document).on('click', '.block_user_button', function() {
            $.ajax({
                type: 'POST',
                url: './admin/db/block_user.php',
                data: {code: $(this).attr('data-id')},
                success: function(response) {
                    let parsed_response = JSON.parse(response);
                    let response_data = parsed_response.data;
                    let response_message = parsed_response.message;
                    if(response_data != ""){
                        $('.block_user_button').html(response_data);
                        if($('.block_user_button').hasClass('block_user')){
                            $('.block_user_button').removeClass('block_user').addClass('unblock_user');
                        }else{
                            $('.block_user_button').removeClass('unblock_user').addClass('block_user');
                        }
                    }
                    notifyshow(response_message, '');
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: users block/unblock user", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //reset devices user
        $(document).on('click', '.devices_list_button', function() {
            let email_user = $(this).attr('data-id');
            var devices_remove = $(this).parent().parent().find('.device');
            let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
            '<h3>'+lang_text['admin']['users']['reset']['all_devices']['confirm']+'</h3>'+
            '<div class="confirm_window_buttons">'+
                '<button class="confirm_window_button_yes">'+lang_text['admin']['users']['reset']['all_devices']['confirm_yes']+'</button>'+
                '<button class="confirm_window_button_no">'+lang_text['admin']['users']['reset']['all_devices']['confirm_no']+'</button>'+
            '</div></div></div>';
            $('body').append(confirm_window);
            $('.confirm_window').css("display", "flex").hide().fadeIn(300);
            $('.confirm_window_button_no').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                }, 300);
            });
            $('.confirm_window_button_yes').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                    $.ajax({
                        type: 'POST',
                        url: './admin/db/reset_devices.php',
                        data: {email: email_user},
                        success: function(response) {
                            devices_remove.remove();
                            let devices_count = $('.users_user_settings_content .device').length;
                            $('.device_count').html(devices_count);
                            let address_count = $('.users_user_settings_content .addresses li').length;
                            $('.address_count').html(address_count);
                            notifyshow(response, '');
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: users reset devices for user", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                }, 300);
            });
        });
        //reset device user
        $(document).on('click', '.remove_device_button', function() {
            let email_user = $(this).attr('data-id');
            let device_id = $(this).attr('data-name');
            var device_remove = $(this).parent();
            $.ajax({
                type: 'POST',
                url: './admin/db/reset_devices.php',
                data: {email: email_user, name: device_id},
                success: function(response) {
                    device_remove.remove();
                    let devices_count = $('.users_user_settings_content .device').length;
                    $('.device_count').html(devices_count);
                    let address_count = $('.users_user_settings_content .addresses li').length;
                    $('.address_count').html(address_count);
                    notifyshow(response, '');
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: users reset device for user", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //reset devices users
        $(document).on('click', '.reset_all_devices_users', function() {
            let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
            '<h3>'+lang_text['admin']['users']['reset']['all_devices']['confirm']+'</h3>'+
            '<div class="confirm_window_buttons">'+
                '<button class="confirm_window_button_yes">'+lang_text['admin']['users']['reset']['all_devices']['confirm_yes']+'</button>'+
                '<button class="confirm_window_button_no">'+lang_text['admin']['users']['reset']['all_devices']['confirm_no']+'</button>'+
            '</div></div></div>';
            $('body').append(confirm_window);
            $('.confirm_window').css("display", "flex").hide().fadeIn(300);
            $('.confirm_window_button_no').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                }, 300);
            });
            $('.confirm_window_button_yes').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                    $.ajax({
                        type: 'POST',
                        url: './admin/db/reset_devices.php',
                        success: function(response) {
                            notifyshow(response, '');
                            $.ajax({
                                type: 'POST',
                                url: './admin/index.php',
                                data: {navbar : 'users', content: 'users'},
                                success: function(response) {
                                    $('.admin_menu_content').html(response);
                                    run();
                                    if(users_status == false){
                                        users();
                                        users_status = true;
                                    }
                                },
                                error: function(xhr, status, error) {
                                    add_log("admin_menu: users reload after reset devices", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: users reset devices for users", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                }, 300);
            });
        });
        //change device and location limit
        $(document).on('click', '.location_all_user', function() {
            $.ajax({
                type: 'POST',
                url: './admin/db/device_limit.php',
                success: function(response) {
                    response = JSON.parse(response);
                    let device_limit_good = response[0];
                    let device_limit_bad = response[1];
                    let location_limit_good = response[2];
                    let location_limit_bad = response[3];
                    let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
                    '<h3>'+lang_text['admin']['users']['limit']['text_device']+'</h3>'+
                    '<span class="devices_change_form">'+
                    '<label>0 - </label>'+
                    '<input type="number" min=1 class="device_all_input_good" value="'+device_limit_good+'">'+
                    '<label> - </label>'+
                    '<input type="number" min=1 class="device_all_input_bad" value="'+device_limit_bad+'">'+
                    '</span>'+
                    '<h3>'+lang_text['admin']['users']['limit']['text_location']+'</h3>'+
                    '<span class="location_change_form">'+
                    '<label>0 - </label>'+
                    '<input type="number" min=1 class="location_all_input_good" value="'+location_limit_good+'">'+
                    '<label> - </label>'+
                    '<input type="number" min=1 class="location_all_input_bad" value="'+location_limit_bad+'">'+
                    '</span>'+
                    '<div class="confirm_window_buttons">'+
                    '<button class="confirm_window_button_yes">'+lang_text['admin']['users']['limit']['confirm_yes']+'</button>'+
                    '<button class="confirm_window_button_no">'+lang_text['admin']['users']['limit']['confirm_no']+'</button>'+
                    '</div></div></div>';
                    $('body').append(confirm_window);
                    $('.confirm_window').css("display", "flex").hide().fadeIn(300);
                    $('.confirm_window_button_no').click(function() {
                        $('.confirm_window').fadeOut(300);
                        setTimeout(function() {
                            $('.confirm_window').remove();
                        }, 300);
                    });
                    $('.confirm_window_button_yes').click(function() {
                        $('.confirm_window').fadeOut(300);
                        setTimeout(function() {
                            let device_good = $('.device_all_input_good').val();
                            let device_bad = $('.device_all_input_bad').val();
                            let location_good = $('.location_all_input_good').val();
                            let location_bad = $('.location_all_input_bad').val();
                            $('.confirm_window').remove();
                            $.ajax({
                                type: 'POST',
                                url: './admin/db/device_limit.php',
                                data: {device_good: device_good, device_bad: device_bad, location_good: location_good, location_bad: location_bad},
                                success: function(response) {
                                    notifyshow(response, '');
                                    $.ajax({
                                        type: 'POST',
                                        url: './admin/index.php',
                                        data: {navbar : 'users', content: 'users'},
                                        success: function(response) {
                                            $('.admin_menu_content').html(response);
                                            run();
                                            if(users_status == false){
                                                users();
                                                users_status = true;
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            add_log("admin_menu: users reload after change device limit", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                                        }
                                    });
                                },
                                error: function(xhr, status, error) {
                                    add_log("admin_menu: users change device limit", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                                }
                            });
                        }, 300);
                    });
                },
                error: function(xhr, status, error) {
                    add_log("admin_menu: users load device limit for change", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
            });
        });
        //send notification to users
        $(document).on('click', '.notification_all_user', function() {
            let confirm_window = '<div class="confirm_window"><div class="confirm_window_content">'+
            '<h3>'+lang_text['admin']['users']['notification']['all_users']['confirm']+'</h3>'+
            '<input type="text" class="notification_all_input_title" placeholder="'+lang_text['admin']['users']['notification']['all_users']['title_box']+'">'+
            '<input type="text" class="notification_all_input_text" placeholder="'+lang_text['admin']['users']['notification']['all_users']['text_box']+'">'+
            '<div class="confirm_window_buttons">'+
                '<button class="confirm_window_button_yes disabled" disabled="disabled">'+lang_text['admin']['users']['notification']['all_users']['confirm_yes']+'</button>'+
                '<button class="confirm_window_button_no">'+lang_text['admin']['users']['notification']['all_users']['confirm_no']+'</button>'+
            '</div></div></div>';
            $('body').append(confirm_window);
            $('.confirm_window').css("display", "flex").hide().fadeIn(300);
            $('.confirm_window_button_no').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    $('.confirm_window').remove();
                }, 300);
            });
            $('.notification_all_input_title, .notification_all_input_text').keyup(function() {
                if($('.notification_all_input_title').val() != "" && $('.notification_all_input_text').val() != ""){
                    $('.confirm_window_button_yes').removeClass('disabled');
                    $('.confirm_window_button_yes').removeAttr('disabled');
                }else{
                    $('.confirm_window_button_yes').addClass('disabled');
                    $('.confirm_window_button_yes').attr('disabled', 'disabled');
                }
            });
            $('.confirm_window_button_yes').click(function() {
                $('.confirm_window').fadeOut(300);
                setTimeout(function() {
                    let notification_title = $('.notification_all_input_title').val();
                    let notification_text = $('.notification_all_input_text').val();
                    $('.confirm_window').remove();
                    $.ajax({
                        type: 'POST',
                        url: './admin/db/send_notification.php',
                        data: {title: notification_title, text: notification_text},
                        success: function(response) {
                            notifyshow(response, '');
                        },
                        error: function(xhr, status, error) {
                            add_log("admin_menu: users send notification to users", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                            notifyshow(status+" ("+xhr.status+"): "+error, '');
                        }
                    });
                }, 300);
            });
        });
        //sort score table
        $(document).on('click', '.header_score_table_subject, .header_score_table_score, .header_score_table_end_date, .header_score_table_time', function() {
            const scoreTable = document.querySelector('.users_user_settings_content_left_score_content');
            const scoreRows = Array.from(scoreTable.getElementsByClassName('score_table'));
            const sortMethods = {
                subject: (a, b, sortOrder) => {
                    const getSubject = (row) => row.querySelector('span[data-id="subject"]').textContent;
                    if (sortOrder === 'asc') {
                        return getSubject(a).localeCompare(getSubject(b));
                    } else {
                        return getSubject(b).localeCompare(getSubject(a));
                    }
                },
                score: (a, b, sortOrder) => {
                    const getScore = (row) => parseFloat(row.querySelector('span[data-id="score"]').getAttribute('data-name'));
                    if (sortOrder === 'asc') {
                        return getScore(a) - getScore(b);
                    } else {
                        return getScore(b) - getScore(a);
                    }
                },
                endDate: (a, b, sortOrder) => {
                    const getDate = (row) => new Date(row.querySelector('span[data-id="end_date"]').textContent);
                    if (sortOrder === 'asc') {
                        return getDate(a) - getDate(b);
                    } else {
                        return getDate(b) - getDate(a);
                    }
                },
                time: (a, b, sort_order) => {
                    const getTime = (row) => {
                        let time = row.querySelector('span[data-id="total_time"]').textContent.split(":");
                        let seconds = 0;
                        if(time.length == 3){
                            seconds = parseInt(time[0]) * 3600 + parseInt(time[1]) * 60 + parseInt(time[2]);
                        }else if(time.length == 2){
                            seconds = parseInt(time[0]) * 60 + parseInt(time[1]);
                        }else if(time.length == 1){
                            seconds = parseInt(time[0]);
                        }
                        return seconds;
                    };
                    const timeA = getTime(a);
                    const timeB = getTime(b);
                    if (sort_order === 'asc') {
                        return timeA - timeB;
                    } else {
                        return timeB - timeA;
                    }
                }
            };
            function sortScoreTable(sortMethod, sortOrder) {
                scoreRows.sort((a, b) => sortMethods[sortMethod](a, b, sortOrder));
                scoreRows.forEach(row => scoreTable.appendChild(row));
            }
            if($(this).attr('data-id') == "asc"){
                $(this).attr('data-id', 'desc');
                sortScoreTable($(this).attr('data-name'), 'desc');
            }else{
                $(this).attr('data-id', 'asc');
                sortScoreTable($(this).attr('data-name'), 'asc');
            }
        });
        //analytic chart
        function show_chart(){
            if ($('#analytic_chart').length > 0) {
                const ctx = document.getElementById('analytic_chart').getContext('2d');
                let delayed = false;
                if($('body').hasClass('dark-mode')){
                    Chart.defaults.color = 'white';
                    Chart.defaults.scale.grid.color = 'rgba(255, 255, 255, 0.1)';
                }else{
                    Chart.defaults.color = 'black';
                    Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.1)';
                }
                let animation = {
                    onComplete: () => {
                      delayed = true;
                    },
                    delay: (context) => {
                      let delay = 0;
                      if (context.type === 'data' && context.mode === 'default' && !delayed) {
                        delay = context.dataIndex * 300 + context.datasetIndex * 100;
                      }
                      return delay;
                    },
                };
                $.ajax({
                    type: 'POST',
                    data: {email: $('.users_user_settings_content_left_analytic_content_chart').attr('data-id')},
                    url: './admin/db/analytic_chart.php',
                    success: function(response) {
                        let parsed_response_chart = JSON.parse(response);
                        let data = {
                            labels: parsed_response_chart.subject.map(subject => subject.length > 15 ? subject.substring(0, 15) + "..." : subject),
                            datasets: [{
                                label: lang_text['admin']['users']['analytic_chart_label'],
                                data: parsed_response_chart.avg_score,
                                borderWidth: 0,
                                backgroundColor: '#00b6ed',
                            }]
                        };
                        let options = {
                            animation: animation,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            if (context.parsed.y !== null) {
                                                return parsed_response_chart.subject[context.dataIndex] + ': ' + context.parsed.y + '%';
                                            }
                                            return null;
                                        }
                                    }
                                }
                            },
                            responsive: true,
                            maintainAspectRatio: false
                        };
                        let config = {
                            type: 'bar',
                            data: data,
                            options: options,
                        };
                        new Chart(ctx, config);
                        $(window).resize(function() {
                            change_font_size();
                        });
                        function change_font_size(){
                            if($(window).width() < 768){
                                Chart.defaults.font.size = ($(window).width()/100) + 5;
                            }else{
                                Chart.defaults.font.size = ($(window).width()/100) + 2;
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        add_log("admin_menu: users load user chart", "AJAX: "+error, "admin.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                });
            }
        }
    }
});