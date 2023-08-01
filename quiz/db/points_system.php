<?php
function calculate_points($points_correct, $points_incorrect, $points_halfcorrect, $correct_analytic, $incorrect_analytic, $halfcorrect_analytic, $checked_analytic, $max_checked_analytic, $count_analytic){
    if($count_analytic == 0){
        return number_format($points_incorrect,8, '.', '');
    }else if ($count_analytic > 0 && $count_analytic < 4){
        //correct
        $points_analytic_correct = number_format(
            $points_correct*($correct_analytic/$count_analytic)*($checked_analytic/$max_checked_analytic)
        ,8);
        //incorrect
        $points_analytic_incorrect = number_format(
            $points_incorrect*($incorrect_analytic/$count_analytic)*($checked_analytic/$max_checked_analytic)
        ,8);
        //halfcorrect
        $points_analytic_halfcorrect = number_format(
            $points_halfcorrect*($halfcorrect_analytic/$count_analytic)*($checked_analytic/$max_checked_analytic)
        ,8);
        //sum
        $points_analytic = number_format(
            $points_analytic_correct + $points_analytic_incorrect + $points_analytic_halfcorrect+($points_incorrect/$count_analytic)
        ,8, '.', '');
        $points_analytic = $points_analytic > $points_incorrect ? number_format($points_incorrect,8, '.', '') : $points_analytic;
    }else{
        //correct
        $points_analytic_correct = number_format(
            $points_correct*($correct_analytic/$count_analytic)*($checked_analytic/$max_checked_analytic)
        ,8);
        //incorrect
        $points_analytic_incorrect = number_format(
            $points_incorrect*($incorrect_analytic/$count_analytic)*($checked_analytic/$max_checked_analytic)
        ,8);
        //halfcorrect
        $points_analytic_halfcorrect = number_format(
            $points_halfcorrect*($halfcorrect_analytic/$count_analytic)*($checked_analytic/$max_checked_analytic)
        ,8);
        //sum
        $points_analytic = number_format(
            $points_analytic_correct + $points_analytic_incorrect + $points_analytic_halfcorrect
        ,8, '.', '');
    }
    return $points_analytic;
}
?>