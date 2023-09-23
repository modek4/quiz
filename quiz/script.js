//logs
function add_log(title, text, user, destination, data){
  $.ajax({
      type: 'POST',
      url: 'log.php',
      data: {title: title, text: text, user: user, destination: destination, data: data},
  });
}
//notify
function notifyshow(message, type) {
  $.notify(message, {
      position: "top right",
      className: type,
      autoHideDelay: 5000,
      icon: null,
  });
  $(".notifyjs-wrapper").addClass("custom-color");
}
//user status
window.addEventListener('online', () => {
  changeStatus("online");
  notifyshow(lang_text['quiz']['online'], '');
});
window.addEventListener('offline', () => {
  notifyshow(lang_text['quiz']['offline'], '');
});
function changeStatus(status) {
  $.ajax({
    type: "POST",
    url: "db/update_status.php",
    data: {status: status},
    async: false,
    error: function(xhr, status, error) {
      add_log("index: change status", "AJAX: "+error, "script.js", "./logs/", xhr.status);
    }
  });
}
$(document).ready(function() {
  setInterval(function() {
    changeStatus("online");
  }, 10000);
});
//logout
$(document).ready(function() {
  $('.logout').click(function() {
    changeStatus("offline");
    window.location.href = "logout.php";
  });
});
//resize menu
document.documentElement.style.setProperty('--window-height', `${window.innerHeight}px`);
window.addEventListener('resize', () => {
  document.documentElement.style.setProperty('--window-height', `${window.innerHeight}px`);
});
const save_score_table = document.getElementById('menu');
const close_save_button_IMG = document.querySelector('#close_save img');
const body = document.body;
//save score table style
function closeSaveScore() {
  if(save_score_table.classList.contains('hide')) {
    save_score_table.style.top = '0';
    body.classList.add('no-scroll');
    save_score_table.classList.add('show');
    save_score_table.classList.remove('hide');
    close_save_button_IMG.style.transform = 'rotate(-90deg)';
  } else {
    save_score_table.style.top = '-95%';
    body.classList.remove('no-scroll');
    close_save_button_IMG.style.transform = 'rotate(90deg)';
    save_score_table.classList.add('hide');
    save_score_table.classList.remove('show');
  }
}
//settings
$(document).ready(function() {
  $('#show_settings_button').click(function() {
    $.ajax({
      type: "POST",
      url: "db/show_settings.php",
      success: function(response) {
        $('.settings_menu').fadeIn(500);
        $('.settings_menu').html(response);
        $('.close_score_settings').click(function() {
          $('.settings_menu').fadeOut(500);
        });
        $('.settings_menu_main_background').click(function() {
          $('.close_score_settings').click();
        });
      },
      error: function(xhr, status, error) {
          add_log("index: settings", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
});
//darkmode
$(document).ready(function() {
  function darkmodeCheck(){
    if (sessionStorage.getItem("dark") == 1) {
      document.body.classList.remove("light-mode");
      document.body.classList.add("dark-mode");
      $('.darkmode').html("<i class=\"fa-regular fa-lightbulb\"></i>");
    }else{
      document.body.classList.remove("dark-mode");
      document.body.classList.add("light-mode");
      $('.darkmode').html("<i class=\"fa-solid fa-lightbulb\"></i>");
    }
  }
  $('.darkmode').click(function() {
    $.ajax({
      type: "POST",
      url: "db/darkmode.php",
      success: function() {
        sessionStorage.setItem("dark", sessionStorage.getItem("dark") == 1 ? 0 : 1);
        darkmodeCheck();
      },
      error: function(xhr, status, error) {
          add_log("index: darkmode", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
  darkmodeCheck();
});
//show score
$(document).ready(function() {
  $('#show_score_button').click(function() {
    $('#show_table').css({"-webkit-transform":"translateX(0%)"});
    $.ajax({
      type: "POST",
      url: "db/show_score.php",
      success: function(response) {
        $('#show_table').html(response);
        $('.close_score_show').click(function() {
          $('#show_table').css({"-webkit-transform":"translateX(-110%)"});
        });
      },
      error: function(xhr, status, error) {
          add_log("index: show score", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
});
//relunch quiz
$(document).ready(function() {
  $('.load_session_quiz_content_no, .load_session_quiz_content_yes').click(function() {
    let code = $('#session_quiz_name').attr('data-code');
    let relaunch = $(this).hasClass('load_session_quiz_content_yes') ? 1 : 0;
    $.ajax({
      type: "POST",
      url: "db/relaunch.php",
      data: {code: code, relaunch: relaunch},
      success: function(response) {
        $('.load_session_quiz_content').fadeOut(300);
        setTimeout(function() {
          $('.load_session_quiz').remove();
        }, 400);
        let response_json = JSON.parse(response);
        if(response_json.variation != 0){
          document.querySelector('.select_subject_select_btn span').innerText = response_json.subject;
          addSubject(response_json.subject);
          document.querySelector(".select_subject_numberOfQuestions").style.display = "block";
          document.getElementById('numberOfQuestions_5').checked = true;
          response_json.number_of_questions == 10 ? document.getElementById('numberOfQuestions_10').checked = true : (
            response_json.number_of_questions == 20 ? document.getElementById('numberOfQuestions_20').checked = true : (
              response_json.number_of_questions == 40 ? document.getElementById('numberOfQuestions_40').checked = true : (
                response_json.number_of_questions == 1 ? document.getElementById('numberOfQuestions_5').checked = true : (
                  document.getElementById('numberOfQuestions_free').checked = true
                )
              )
            )
          );
          document.querySelector(".select_subject_numberOfQuestions_max").innerHTML = "";
          if(document.getElementById('numberOfQuestions_free').checked == true){
            document.querySelector(".select_subject_numberOfQuestions_free").style.display = "block";
            document.querySelector(".select_subject_numberOfQuestions_free input").value = response_json.number_of_questions;
          }
          document.querySelector('.quiz_score').style.display = "flex";
          document.getElementById('score_quiz').innerHTML = response_json.points;
          document.getElementById('score_quiz_all').innerHTML = response_json.number_of_questions;
          var relaunch_timer = response_json.date;
          $.ajax({
            type: "POST",
            url: "db/show_quiz.php",
            data: {subject: response_json.subject, number_of_questions: response_json.number_of_questions, relaunch: 1},
            success: function(response) {
              $('#quiz_load').html(response);
              window.scrollTo(0, 0);
              document.getElementById('menu').classList.add('show');
              save_score_table.style.top = '-95%';
              body.classList.remove('no-scroll');
              close_save_button_IMG.style.transform = 'rotate(90deg)';
              save_score_table.classList.add('hide');
              save_score_table.classList.remove('show');
              $('#close_save').attr('onclick', 'closeSaveScore()');
              timer(true, relaunch_timer);
              run_quiz();
            }
          });
        }else{
          $.ajax({
            type: "POST",
            url: "db/save_score.php",
            data: {score: response_json.points, question_count: response_json.number_of_questions,subject: response_json.subject, quiz_start: response_json.date, relaunch: 1},
            success: function(response) {
              notifyshow(response, '');
              $.ajax({
                type: "POST",
                url: "db/notifications.php",
                data: {id: "bellreload"},
                success: function(response) {
                  $('.notification_bell').html(response);
                  menu.classList.remove('show');
                  menu.classList.add('hide');
                  $('.notification_background').css('display', 'none');
                  $('#save_score').html("<i class='fa-regular fa-floppy-disk'></i>");
                },
                error: function(xhr, status, error) {
                    add_log("index: reload notification bell", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
              });
            },
            error: function(xhr, status, error) {
                add_log("index: relaunch quiz decline", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                notifyshow(status+" ("+xhr.status+"): "+error, '');
            }
          });
        }
      }
    });
  });
});
//timer
let timerInterval;
let quiz_start;
function timer(run = true, relaunch = null) {
  if (run) {
    if (timerInterval !== undefined) {
      clearInterval(timerInterval);
    }
    quiz_start = new Date();
    if(relaunch !== null){
      quiz_start = new Date(quiz_start.getTime() - relaunch*1000);
    }
    timerInterval = setInterval(function () {
      var now = new Date().getTime();
      var distance = now - quiz_start;
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);
      if (hours < 10) {
        hours = '0' + hours;
      }
      if (minutes < 10) {
        minutes = '0' + minutes;
      }
      if (seconds < 10) {
        seconds = '0' + seconds;
      }
      $('#quiz_timer').text(hours + ':' + minutes + ':' + seconds);
    }, 1000);
  } else {
    clearInterval(timerInterval);
  }
}
$('#save_score').click(function() {
  timer(false);
});
//lightbox
$('#lightbox').click(function() {
  closeLightbox();
});
function open_lightbox(image) {
  var lightbox = document.getElementById('lightbox');
  var img = lightbox.getElementsByTagName('img')[0];
  img.src = image.src;
  lightbox.style.display = 'block';
  body.classList.add('no-scroll');
}
function closeLightbox() {
  var lightbox = document.getElementById('lightbox');
  lightbox.style.display = 'none';
  body.classList.remove('no-scroll');
}
//show quiz
let one_to_one = false;
$(document).ready(function() {
  $('.select_subject_submit').click(function(e) {
    var number_of_questions = Math.round(document.querySelector('.select_subject_numberOfQuestions_free input').value);
    var subject = document.querySelector(".select_subject_wrapper").querySelector('.select_subject_select_btn').firstElementChild.innerText;
    document.querySelector('.select_subject_submit span').innerText = lang_text['quiz']['select_quiz']['load_quiz'];
    if(number_of_questions==1){
      one_to_one = true;
    }
    $.ajax({
      type: "POST",
      data: {subject: subject, number_of_questions: number_of_questions},
      url: "db/show_quiz.php",
      success: function(response) {
        $('#quiz_load').html(response);
        document.querySelector('.select_subject_submit span').innerText = lang_text['quiz']['select_quiz']['start_quiz'];
        window.scrollTo(0, 0);
        document.querySelector('.quiz_score').style.display = "flex";
        document.getElementById('score_quiz').innerHTML = question_count_start;
        $('#save_score').removeAttr('disabled');
        if(document.getElementById('score_quiz_all') !== null){
          if(question_count>0){
            if(one_to_one){
              document.getElementById('score_quiz_all').innerHTML = 1.00;
            }else{
              document.getElementById('score_quiz_all').innerHTML = question_count;
            }
          }else{
            document.getElementById('score_quiz_all').innerHTML = 0.00;
          }
        }
        document.getElementById('menu').classList.add('show');
        save_score_table.style.top = '-95%';
        body.classList.remove('no-scroll');
        close_save_button_IMG.style.transform = 'rotate(90deg)';
        save_score_table.classList.add('hide');
        save_score_table.classList.remove('show');
        $('#close_save').attr('onclick', 'closeSaveScore()');
        timer(true);
        run_quiz();
      },
      error: function(xhr, status, error) {
          add_log("index: show quiz", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
});
//quiz mechanics
function run_quiz(){
  //report
  var report_buttons = document.querySelectorAll('.report_question');
  var answers = [];
  report_buttons.forEach(function(button) {
    button.addEventListener('click', function() {
      var parent = button.parentNode;
      if (parent.classList.contains('report_question_active')) {
        parent.classList.remove('report_question_active');
        parent.querySelector('.report_question').innerHTML = lang_text['quiz']['report']['title_send'];
        if (parent.querySelector('h3')) {
          parent.querySelector('h3').remove();
        }
        var all_checked = parent.querySelectorAll('.radio');
        all_checked.forEach(function(checked, index) {
          if(answers[index].status == 1) {
            checked.classList.add('checked');
          } else if(answers[index].status == 2){
            checked.classList.add('correct');
          }else if(answers[index].status == 3){
            checked.classList.add('incorrect');
          }else if(answers[index].status == 4){
            checked.classList.add('correct');
            checked.classList.add('checked');
          }else if(answers[index].status == 5){
            checked.classList.add('incorrect');
            checked.classList.add('checked');
          }else{
            checked.classList.remove('checked');
            checked.classList.remove('correct');
            checked.classList.remove('incorrect');
            checked.classList.remove('checked_report');
          }
        });
      } else {
        var all_checked = parent.querySelectorAll('.radio');
        answers = [];
        all_checked.forEach(function(checked) {
          var answer = {
            status: 0
          };
          if (checked.classList.contains('checked')) {
            answer.status = 1;
            if (checked.classList.contains('correct')) {
              answer.status += 3;
            } else if (checked.classList.contains('incorrect')) {
              answer.status += 4;
            }
          }else{
            if (checked.classList.contains('correct')) {
              answer.status = 2;
            } else if (checked.classList.contains('incorrect')) {
              answer.status = 3;
            }
          }
          answers.push(answer);
          checked.classList.remove('checked');
          checked.classList.remove('correct');
          checked.classList.remove('incorrect');
          checked.classList.remove('checked_report');
        });
        parent.classList.add('report_question_active');
        parent.querySelector('.report_question').innerHTML = lang_text['quiz']['report']['title_decline'];
        parent.querySelector('h4').innerHTML += '<h3>'+lang_text['quiz']['report']['text']+'<br/><button class=\"send_report_button\">'+lang_text['quiz']['report']['button']+'</button></h3>';
        parent.querySelectorAll('.radio').forEach(function(checked) {
          checked.addEventListener('click', function() {
            checked.classList.add('checked_report');
          });
        });
      }
      var sendReportButton = parent.querySelector('.send_report_button');
      if(sendReportButton) {
        var all_checked = parent.querySelectorAll('.radio');
        sendReportButton.addEventListener('click', function() {
          var ListOfChecked = [];
          var SubjectNameReport = '';
          var QuestionIdReport = '';
          all_checked.forEach(function (checked){
            if(checked.classList.contains('checked_report')){
              ListOfChecked.push(checked.getAttribute('value'));
            }
            SubjectNameReport = checked.getAttribute('subject');
            QuestionIdReport = checked.getAttribute('name');
          });
          ListOfChecked.sort();
          var ListOfCheckedReport = ListOfChecked.join();
          QuestionIdReport = QuestionIdReport.substring(9);
          if(QuestionIdReport == '' || SubjectNameReport == '') {
            return false;
          }
          if(ListOfCheckedReport == '') {
            ListOfCheckedReport = 0;
          }
          $.ajax({
            type: 'POST',
            url: 'db/report_question.php',
            data: {selected_correct: ListOfCheckedReport, subject: SubjectNameReport, report_id: QuestionIdReport},
            success: function(response) {
              notifyshow(response, '');
              parent.classList.remove('report_question_active');
              parent.querySelector('.report_question').innerHTML = lang_text['quiz']['report']['title_send'];
              if (parent.querySelector('h3')) {
                parent.querySelector('h3').remove();
              }
              var all_checked = parent.querySelectorAll('.radio');
              all_checked.forEach(function(checked, index) {
                if(answers[index].status == 1) {
                  checked.classList.add('checked');
                } else if(answers[index].status == 2){
                  checked.classList.add('correct');
                }else if(answers[index].status == 3){
                  checked.classList.add('incorrect');
                }else if(answers[index].status == 4){
                  checked.classList.add('correct');
                  checked.classList.add('checked');
                }else if(answers[index].status == 5){
                  checked.classList.add('incorrect');
                  checked.classList.add('checked');
                }
              });
              $.ajax({
                type: 'POST',
                url: 'db/notifications.php',
                data: {id: 'bellreload'},
                success: function(response) {
                  $('.notification_bell').html(response);
                },
                error: function(xhr, status, error) {
                    add_log('index: reload bell after report question', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                    notifyshow(status+' ('+xhr.status+'): '+error, '');
                }
              });
            },
            error: function(xhr, status, error) {
                add_log('index: report question', 'AJAX: '+error, 'script.js', './logs/', xhr.status);
                notifyshow(status+' ('+xhr.status+'): '+error, '');
            }
          });
        });
      }
    });
  });
  if(document.getElementById('quiz_results')!==null) {
    const quiz_results = document.getElementById('quiz_results');
    var final_score = 0;
    if(one_to_one){
      var count_one_to_one = 1;
      var correct = 0;
    }
    quiz_results.addEventListener('click', (event) => {
      if(!event.target.classList.contains('radio') || event.target.classList.contains('checked') || event.target.classList.contains('verification')) {
        return;
      }
      if(event.target.parentElement.classList.contains('report_question_active')) {
        return;
      }
      const selected_answer = event.target;
      const subject = selected_answer.getAttribute('subject');
      const question_number = selected_answer.getAttribute('name');
      const question_number_short = question_number.substring(9);
      var correct_answer = "";
      selected_answer.classList.add('verification');
      $.ajax({
        url: 'db/quiz_correct.php',
        type: 'POST',
        data: {question_number: question_number_short, subject: subject},
        success: function(data){
          correct_answer = data;
          if(correct_answer == "valid"){
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_answer.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200){
                  window.location.reload();
                }
            };
            xhr.send();
            return;
          }
          selected_answer.classList.remove('verification');
          var max_checked = correct_answer.length;
          const all_answers = quiz_results.querySelectorAll(`[name="${question_number}"]`);
          let checked_count = 0;
          let in_correct = 0;
          all_answers.forEach(input => {
            if (input.classList.contains('checked')) {
              checked_count++;
            }
            if(input.classList.contains('incorrect')) {
              checked_count = max_checked;
            }
          });
          if (checked_count === max_checked || checked_count >= max_checked) {
            return;
          } else {
            selected_answer.classList.add('checked');
            all_answers.forEach(answer => {
              if (answer.classList.contains('checked')) {
                const matches = correct_answer.match(answer.getAttribute('value'));
                if (matches) {
                  if(!answer.classList.contains('correct')){
                    answer.classList.add('correct');
                    final_score += 1.0000000000/max_checked;
                    if(one_to_one){
                      correct += 1.0000000000/max_checked;
                    }
                  }
                } else {
                  if(!answer.classList.contains('incorrect')){
                    answer.classList.add('incorrect');
                    in_correct++;
                  }
                }
              }
            });
            let check_analytic = document.querySelectorAll(`[name="question-${question_number_short}"].checked`).length;
            let check_analytic_answer = "";
            document.querySelectorAll(`[name="question-${question_number_short}"].checked`).forEach(answer => {
              check_analytic_answer += answer.getAttribute('value');
            });
            let check_analytic_answer_number =selected_answer.parentElement.querySelector('.number_of_all_questions').innerHTML;
            if(!one_to_one){
              check_analytic_answer_number = check_analytic_answer_number.substring(0, check_analytic_answer_number.indexOf('/'));
              check_analytic_answer_number = check_analytic_answer_number.replace(/^\s+|\s+$/gm,'');
            }
            let correct_analytic = document.querySelectorAll(`[name="question-${question_number_short}"].correct`).length;
            let incorrect_analytic = document.querySelectorAll(`[name="question-${question_number_short}"].incorrect`).length;
            var analytic = [
              ["subject", subject],
              ["id_question", question_number_short],
              ["checked",check_analytic],
              ["correct",correct_analytic],
              ["incorrect", incorrect_analytic],
              ["maxchecked", max_checked]];
            var selected_answers = [
              ["id", check_analytic_answer_number],
              ["subject", subject],
              ["id_question", question_number_short],
              ["answers",check_analytic_answer],
              ["correct_answers",correct_answer]];
            if(in_correct == 1){
              all_answers.forEach(answer => {
                const matches_incorrect = correct_answer.match(answer.getAttribute('value'));
                if (matches_incorrect) {
                  answer.classList.add('correct');
                }
              });
            }
            if(max_checked == document.querySelectorAll(`[name="question-${question_number_short}"].correct`).length){
              $.ajax({
                type: "POST",
                url: "db/analytic_change.php",
                data: {analytic: analytic},
                success: function() {
                  $.ajax({
                    type: "POST",
                    url: "db/analytic_change.php",
                    data: {selected_answers: selected_answers},
                    success: function() {
                      $.ajax({
                        type: "POST",
                        url: "db/auto_save.php",
                        success: function(response) {
                          if(response == "success"){
                            $('#save_score').click();
                            closeSaveScore();
                            timer(false);
                          }
                        }
                      });
                    },
                    error: function(xhr, status, error) {
                        add_log("index: score change", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                  });
                },
                error: function(xhr, status, error) {
                    add_log("index: analytic change", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
              });
            }
            if ((one_to_one && in_correct == 1) || (one_to_one && correct.toFixed(2) == 1)) {
              correct = 0;
              let next_question = count_one_to_one + 1;
              if(document.querySelector(".background_question:nth-child("+next_question+")")){
                let questions = document.querySelector(".background_question:nth-child("+next_question+")");
                questions.classList.remove('hidden_question');
                document.getElementById('score_quiz_all').innerHTML = count_one_to_one;
                count_one_to_one++;
              }else{
                document.getElementById('score_quiz_all').innerHTML = next_question - 1;
              }
            }
          }
          document.getElementById('score_quiz').innerHTML = final_score.toFixed(2);
        },
        error: function(xhr, status, error) {
          add_log("index: quiz correct", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
        }
      });
    });
  }
}
//confetti
function confetti(){
  const canvas_create = document.createElement('canvas');
  canvas_create.id = 'canvas_confetti';
  canvas_create.style.cssText = 'width:100%;height:100%;position:absolute;top:0;left:0;margin:0;overflow:hidden';
  document.getElementById('menu').appendChild(canvas_create);
  let W = window.innerWidth;
  let H = window.innerHeight;
  const canvas = document.getElementById("canvas_confetti");
  const context = canvas.getContext("2d");
  const maxConfettis = 150;
  const particles = [];
  const possibleColors = [
    "DodgerBlue",
    "OliveDrab",
    "Gold",
    "Pink",
    "SlateBlue",
    "LightBlue",
    "Gold",
    "Violet",
    "PaleGreen",
    "SteelBlue",
    "SandyBrown",
    "Chocolate",
    "Crimson"
  ];

  function randomFromTo(from, to) {
    return Math.floor(Math.random() * (to - from + 1) + from);
  }

  function confettiParticle() {
    this.x = Math.random() * W; // x
    this.y = Math.random() * H - H; // y
    this.r = randomFromTo(11, 33); // radius
    this.d = Math.random() * maxConfettis + 11;
    this.color = possibleColors[Math.floor(Math.random() * possibleColors.length)];
    this.tilt = Math.floor(Math.random() * 33) - 11;
    this.tiltAngleIncremental = Math.random() * 0.07 + 0.05;
    this.tiltAngle = 0;

    this.draw = function() {
      context.beginPath();
      context.lineWidth = this.r / 2;
      context.strokeStyle = this.color;
      context.moveTo(this.x + this.tilt + this.r / 3, this.y);
      context.lineTo(this.x + this.tilt, this.y + this.tilt + this.r / 5);
      return context.stroke();
    };
  }
  function Draw() {
    const results = [];
    requestAnimationFrame(Draw);
    context.clearRect(0, 0, W, window.innerHeight);
    for (var i = 0; i < maxConfettis; i++) {
      results.push(particles[i].draw());
    }
    let particle = {};
    let remainingFlakes = 0;
    for (var i = 0; i < maxConfettis; i++) {
      particle = particles[i];

      particle.tiltAngle += particle.tiltAngleIncremental;
      particle.y += (Math.cos(particle.d) + 3 + particle.r / 2) / 2;
      particle.tilt = Math.sin(particle.tiltAngle - i / 3) * 15;

      if (particle.y <= H) remainingFlakes++;
      if (particle.x > W + 30 || particle.x < -30 || particle.y > H) {
        particle.x = Math.random() * W;
        particle.y = -30;
        particle.tilt = Math.floor(Math.random() * 10) - 20;
      }
    }
    return results;
  }
  window.addEventListener(
    "resize",
    function() {
      W = window.innerWidth;
      H = window.innerHeight;
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    },
    false
  );
  for (var i = 0; i < maxConfettis; i++) {
    particles.push(new confettiParticle());
  }
  canvas.width = W;
  canvas.height = H;
  Draw();
  setTimeout(function() {
    $('#canvas_confetti').fadeOut();
    setTimeout(function() {
      $('#canvas_confetti').remove();
    }, 1500);
  }, 1500);
}
//save score
$('#save_score').click(function() {
  $('#close_save').removeAttr('onclick');
  if($('#save_score').attr('disabled') == 'disabled') {
    return false;
  }
  $('#save_score').attr('disabled', 'disabled');
  $('#save_score').html('<i class=\"fa-regular fa-floppy-disk fa-beat-fade\" style=\"--fa-beat-fade-opacity: 0.67; --fa-beat-fade-scale: 1.075;\"></i>');
  if(question_count != 0) {
    timer(false);
    if(parseFloat($('#score_quiz').text()) / parseFloat($('#score_quiz_all').text()) >= 0.92) {
      confetti();
    }
    $.ajax({
      type: "POST",
      url: "db/save_score.php",
      data: {score: $('#score_quiz').text(), question_count: $('#score_quiz_all').text(),subject: subject, quiz_start: $('#quiz_timer').text()},
      success: function(response) {
        notifyshow(response, '');
        $.ajax({
          type: "POST",
          url: "db/notifications.php",
          data: {id: "bellreload"},
          success: function(response) {
            $('.notification_bell').html(response);
            menu.classList.remove('show');
            menu.classList.add('hide');
            $('.notification_background').css('display', 'none');
            $('#save_score').html("<i class='fa-regular fa-floppy-disk'></i>");
          },
          error: function(xhr, status, error) {
              add_log("index: reload notification bell", "AJAX: "+error, "script.js", "./logs/", xhr.status);
              notifyshow(status+" ("+xhr.status+"): "+error, '');
          }
        });
      },
      error: function(xhr, status, error) {
          add_log("index: save score", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  }
});
//notifications
var notification = document.querySelectorAll('.notification');
var notification_array = Array.prototype.slice.call(notification,0);
notification_array.forEach(function(el){
  var button = el.querySelector('a[data-toggle="notification"]'),
      menu = el.querySelector('.notification_menu');
  $('.notification_background').click(function() {
    $.ajax({
      type: "POST",
      url: "db/notifications.php",
      data: {id: "bellreload"},
      success: function(response) {
        $('.notification_bell').html(response);
        menu.classList.remove('show');
        menu.classList.add('hide');
        $('.notification_background').css('display', 'none');
      },
      error: function(xhr, status, error) {
          add_log("index: close notification", "AJAX: "+error, "script.js", "./logs/", xhr.status);
          notifyshow(status+" ("+xhr.status+"): "+error, '');
      }
    });
  });
  button.onclick = function(event) {
    if(!menu.hasClass('show')) {
      var arrow = button.querySelector('i.fa-bell')
      if(arrow.hasClass('bell_animation')) {
        arrow.classList.remove('bell_animation');
      }
      $.ajax({
        type: "POST",
        url: "db/notifications.php",
        success: function(response) {
          $('.notification_menu').html(response);
          menu.classList.add('show');
          menu.classList.remove('hide');
          var flag_elements = document.querySelectorAll('.fa-flag');
          var all_notifications = document.querySelector('.clear_all_notification');
          if(all_notifications){
            all_notifications.onclick = function() {
              $.ajax({
                type: "POST",
                url: "db/notifications.php",
                data: {id: 'all'},
                success: function() {
                  $.ajax({
                    type: "POST",
                    url: "db/notifications.php",
                    data: {id: "bellreload"},
                    success: function(response) {
                      $('.notification_bell').html(response);
                      menu.classList.remove('show');
                      menu.classList.add('hide');
                      $('.notification_background').css('display', 'none');
                    },
                    error: function(xhr, status, error) {
                        add_log("index: close notification after remove all", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                        notifyshow(status+" ("+xhr.status+"): "+error, '');
                    }
                  });
                },
                error: function(xhr, status, error) {
                    add_log("index: delete all notifications", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
              });
            }
          }
          flag_elements.forEach((flag) => {
            flag.addEventListener('click', () => {
              var flagid = flag.getAttribute('data-id');
              flag.classList.remove('fa-solid');
              $.ajax({
                type: "POST",
                url: "db/notifications.php",
                data: {id: flagid},
                success: function() {
                  flag.classList.add('fa-regular');
                },
                error: function(xhr, status, error) {
                    add_log("index: remove one notification", "AJAX: "+error, "script.js", "./logs/", xhr.status);
                    notifyshow(status+" ("+xhr.status+"): "+error, '');
                }
              });
            });
          });
        },
        error: function(xhr, status, error) {
            add_log("index: load notification", "AJAX: "+error, "script.js", "./logs/", xhr.status);
            notifyshow(status+" ("+xhr.status+"): "+error, '');
        }
      });
      $('.notification_background').css('display', 'block');
    }else {
      $.ajax({
        type: "POST",
        url: "db/notifications.php",
        data: {id: "bellreload"},
        success: function(response) {
          $('.notification_bell').html(response);
          menu.classList.remove('show');
          menu.classList.add('hide');
          $('.notification_background').css('display', 'none');
        },
        error: function(xhr, status, error) {
            add_log("index: reload bell", "AJAX: "+error, "script.js", "./logs/", xhr.status);
            notifyshow(status+" ("+xhr.status+"): "+error, '');
        }
      });
    }
  };
})

Element.prototype.hasClass = function(class_name) {
    return this.class_name && new RegExp("(^|\\s)" + class_name + "(\\s|$)").test(this.class_name);
};
//select subject form
  const wrapper = document.querySelector(".select_subject_wrapper"),
  select_Btn = wrapper.querySelector(".select_subject_select_btn"),
  search_Inp = wrapper.querySelector(".select_subject_search input"),
  options = wrapper.querySelector(".select_subject_options");
  var subjects = [];
  var subjects_questions = [];
  var term_subject = [];
  $.ajax({
    url: 'db/subject_list.php',
    type: 'POST',
    dataType: 'json',
    success: function(response) {
      subjects = response.subjects;
      subjects_questions = response.subject_questions;
      if(subjects.length != 0){
        addSubject();
        subjects.forEach(subject => {
          term_subject.push(subject[0]);
        });
      }else{
        notifyshow(subjects_questions, '');
      }
    },
    error: function(xhr, status, error) {
        add_log("index: load subjects", "AJAX: "+error, "script.js", "./logs/", xhr.status);
        notifyshow(status+" ("+xhr.status+"): "+error, '');
    }
  });
  function addSubject(selected_subject) {
    options.innerHTML = "";
    let term = 0;
    subjects.forEach(subject => {
      var name_subject = subject[0];
      let li;
      let is_selected = name_subject == selected_subject ? "selected subject_li" : "subject_li";
      if(term != subject[1]){
        term=subject[1];
        li = `<li class="subject_li_term">${lang_text['quiz']['select_quiz']['term']} ${term}</li>
        <li onclick="updateName(this)" class="${is_selected}">${name_subject}</li>`;
      }else{
        li = `<li onclick="updateName(this)" class="${is_selected}">${name_subject}</li>`;
      }
      options.insertAdjacentHTML("beforeend", li);
    });
    document.querySelector(".select_subject_submit").style.display = "none";
    document.querySelector(".select_subject_numberOfQuestions_free").style.display = "none";
    document.querySelectorAll(".radio_buttons input").forEach(radio => {
      radio.checked = false;
    });
    document.querySelector("#custom_numberOfQuestions").value = "";
    document.querySelector(".select_subject_numberOfQuestions_max").innerHTML = parseInt($('.select_subject_select_btn input').attr("max"));
    document.querySelector("#custom_numberOfQuestions").max = parseInt($('.select_subject_select_btn input').attr("max"));
    document.querySelector(".select_subject_numberOfQuestions_max").addEventListener("click", () => {
      document.querySelector("#custom_numberOfQuestions").value = parseInt($('.select_subject_select_btn input').attr("max"));
      document.querySelector("#custom_numberOfQuestions").dispatchEvent(new Event('change'));
    });
  }
  document.querySelector("#custom_numberOfQuestions").addEventListener("change", () => {
    let min_question_value = parseInt($('#custom_numberOfQuestions').attr("min"));
    let max_question_value = parseInt($('#custom_numberOfQuestions').attr("max"));
    let value_question = Math.round(parseInt(document.querySelector("#custom_numberOfQuestions").value));
    if(value_question >= min_question_value && value_question <= max_question_value) {
      document.querySelector(".select_subject_submit").style.display = "block";
      document.querySelector("#custom_numberOfQuestions").value = value_question;
    }else{
      if(document.querySelector(".select_subject_submit").style.display != "block"){
        document.querySelector(".select_subject_submit").style.display = "block";
      }
      if(value_question < min_question_value) {
        document.querySelector("#custom_numberOfQuestions").value = min_question_value;
      }else{
        document.querySelector("#custom_numberOfQuestions").value = max_question_value;
      }
    }
    if(isNaN(value_question)){
      document.querySelector("#custom_numberOfQuestions").value = "";
    }
  });
  search_Inp.addEventListener("keyup", () => {
    var arr = [];
    let search_word = search_Inp.value.toLowerCase().trim();
    arr = term_subject.filter(data => {
      return data.toLowerCase().replace(/\s/g, '').includes(search_word);
    }).map(data => {
      let is_selected = data == select_Btn.firstElementChild.innerText ? "selected subject_li" : "subject_li";
      return `<li onclick="updateName(this)" class="${is_selected}">${data}</li>`;
    }).join("");
    options.innerHTML = arr ? arr : `<p style="margin-top: 10px;">Brak quizu</p>`;
    if(search_Inp.value == ""){
      let selected_subject = arr.match(/(?<=selected subject_li">)(.*?)(?=<\/li>)/g);
      if(selected_subject == null){
        addSubject();
      }else{
        addSubject(selected_subject[0]);
      }
    }
  });
  select_Btn.addEventListener("click", () => {
    wrapper.classList.toggle("active");
    document.querySelector('.quiz_score').style.display = "none";
    $('#close_save').removeAttr('onclick');
  });
  document.addEventListener("click", (e) => {
    if(e.target !== select_Btn && e.target !== search_Inp && e.target !== wrapper.querySelector(".select_subject_select_btn span") && e.target !== wrapper.querySelector(".select_subject_select_btn i") && e.target !== wrapper.querySelector(".select_subject_content") && e.target !== wrapper.querySelector(".subject_li_term") && e.target !== wrapper.querySelector(".select_subject_options")){
      wrapper.classList.remove("active");
    }
  });
function updateName(selected_li) {
  document.querySelector(".select_subject_numberOfQuestions").style.display = "block";
  document.querySelector('.select_subject_select_btn input').max = subjects_questions.find(sq => sq[0] === selected_li.innerText)[1];
  search_Inp.value = "";
  addSubject(selected_li.innerText);
  wrapper.classList.remove("active");
  select_Btn.firstElementChild.innerText = selected_li.innerText;
}
document.querySelectorAll('input[type="radio"]').forEach((item) => {
  item.addEventListener('click', () => {
    if(item == document.getElementById('numberOfQuestions_free')) {
      document.querySelector('.select_subject_numberOfQuestions_free').style.display = 'block';
      document.querySelector("#custom_numberOfQuestions").value = '';
      document.querySelector(".select_subject_submit").style.display = "none";
    }else{
      document.querySelector('.select_subject_numberOfQuestions_free').style.display = 'none';
      document.querySelector(".select_subject_submit").style.display = "block";
      let val = item.value;
      document.querySelector("#custom_numberOfQuestions").value = val;
    }
  });
});
