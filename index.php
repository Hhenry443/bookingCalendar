<?php 

// This gives us a variable, $filteredBookings that has all of the bookings for this month
require './api/getBookings.php';

// Get current month and year from URL if set, otherwise use today's date
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Adjust month and year to ensure valid values
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}
// Set the weekdays for the calendar printing
$weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// set the day limit
$dayCounter = 1;
$dayLimit = 31;

// Set the start flag
$start = false;

function getDayOfWeek($day, $month, $year) {
  // Jan and feb treated as months 13 and 14 of previous year. 
  // This is due to a quirk in Zeller's congruence (the formula we are using) as it was designed a long time ago
  if ($month <= 2) {
    $month += 12;
    $year -= 1;
  }

  // Zeller's Congruence looks a bit like this. 
  // // h = (q + (13 * (m + 1)) / 5 + K + K / 4 + J / 4 + 5 * J) % 7;
  // Where:
  // q = day of month
  // m = month (adjusted for jan and feb)
  // K = year in the century 
  // J = zero based century

  $K = $year % 100;  // Year within century
  $J = intdiv($year, 100);  // Zero-based century

  // Zeller's Congruence implementation
  $h = ($day + intdiv(13 * ($month + 1), 5) + $K + intdiv($K, 4) + intdiv($J, 4) + 5 * $J) % 7;

  // We can now map this to a day of the week. We will use the calendars day column number for this.
  // It is a bit offset as zeller's uses saturday as week day 1, and we use monday
  $days = ["6", "7", "1", "2", "3", "4", "5"];

  return $days[$h];
}

$startCol = getDayOfWeek(1, $month, $year);

// Calculate total number of cells needed
$totalCells = $dayLimit + ($startCol - 1);
$totalRows = ceil($totalCells / 7) + 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Health Advice Group</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://kit.fontawesome.com/b664604463.js" crossorigin="anonymous"></script>
</head>
<body class='bg-[#EFF1ED]'>
  <div class='w-full h-16 shadow-xl flex absolute'>
    <div id='logoContainer' class='w-1/2 h-full flex justify-center items-center'>
      <p>Health Advice Group!</p>
    </div>

    <div id='linkContainer' class='w-1/2 h-full flex justify-center items-center space-x-10'>
      <p>Home</p>
      <p>Booking</p>
      <p>Products</p>
    </div>
  </div>

  <div id='content' class='h-screen flex items-center flex-col'>
    <p class='text-3xl mt-24'>Make a booking</p>
    <div id='bookingCalendar' class='mt-8 w-1/2 flex items-center flex-col'>
      <div id='buttonNavigationRow' class='flex items-center space-x-20'>
        <a href="?month=<?= $month - 1 ?>&year=<?= $year ?>" class='bg-[#717744] px-8 py-2 rounded-lg text-white text-xl flex items-center justify-center hover:cursor-pointer'>
          <i class="fa-solid fa-arrow-left text-2xl" style="color: #FFFFFF;"></i>
        </a>
        <p class='text-2xl'><?= date('F Y', strtotime("$year-$month-01")) ?></p>
        <a href="?month=<?= $month + 1 ?>&year=<?= $year ?>" class='bg-[#717744] px-8 py-2 rounded-lg text-white text-xl flex items-center justify-center hover:cursor-pointer'>
          <i class="fa-solid fa-arrow-right text-2xl" style="color: #FFFFFF;"></i>
        </a>
      </div>

      <?php
      $bookedDates = [];
      foreach ($filteredBookings as $booking) {
          $timestamp = strtotime($booking['booking_date']);
          if ($timestamp !== false) {
              $bookedDates[] = (int)date('j', $timestamp);
          }
      }
      ?>

  <div class='bg-green-500 h-90 w-full mt-8 grid grid-cols-7 grid-rows-<?= $totalRows ?>'>
    <?php for ($row = 1; $row <= 7; $row++): ?>
      <?php for ($col = 1; $col <= 7; $col++): ?>
        <?php 
          $cellID = "{$row}{$col}";
          $isBooked = in_array($dayCounter, $bookedDates) && $start; // Check if the date is booked
          $backgroundClass = $isBooked ? "bg-red-400 cursor-not-allowed" : ($col % 2 == 0 ? "bg-[#D9D9D9]" : "bg-white");
          $hoverClass = (!$isBooked & $start & ($dayCounter <= $dayLimit)) || ($col == $startCol) & ($dayCounter <= $dayLimit) ? "hover:bg-green-200 cursor-pointer" : "";
          $onclickAttr = !$isBooked & $start & ($dayCounter <= $dayLimit) ? "onclick='handleBooking($cellID)'" : "";
        ?>
        
        <div id="<?= $cellID ?>" class='border border-gray-400 flex items-center justify-center <?= $backgroundClass ?> <?= $hoverClass ?>' <?= $onclickAttr ?>>
          <?php if ($row == 1): ?>
            <?= $weekdays[$col - 1] ?>
          <?php else: ?>
            <?php 
              if ($col == $startCol) {
                $start = true;
              }

              if ($start && $dayCounter <= $dayLimit){
                echo "{$dayCounter}"; 
                $dayCounter++;
              }
            ?>
          <?php endif; ?> 
        </div>
      <?php endfor; ?>
    <?php endfor; ?>
  </div>


</div>

    </div>
  </div>

  <script>
    function handleBooking(dateID) {
      // This function will allow me to handle the function. If the date is not yet booked, then the user will be able to make the booking for this date. 
      // It will check this before sending the fetch request with the date. 
    }
  </script>
</body>

</html>