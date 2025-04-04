<?php // HELPER FUNCTIONS FOR THE PUZZLE SOLVING

// Function that extracts the middle array element from an array
// no matter its length. It just divides by two and rounds up.
function ExtractMiddleArrayValue($array)
{
    if (!is_array($array)) {
        throw new Exception("[NOT ARRAY PROVIDED]: Data is not an array, please provide an array.");
    }
    return $array[ceil(count($array) / 2) - 1];
}

// Function that generates a X,Y coordinate variable based on an array
function generateXYCoordinates($data, $splitOn = "")
{
    $coordinateSystem = [];

    // Check if $splitOn is not empty, and then split the data array on that value
    if ($splitOn != "") {
        $data = explode("\n", $data);
    }

    // Check if $splitOn was "\n", then also apply array_map rtrim to remove any trailing whitespace
    if ($splitOn == "\n") {
        $data = array_map('rtrim', $data);
    }

    // Throw error if $splitOn is not chosen and $data is not an array
    if (!is_array($data)) {
        throw new Exception("Data is not an array, please provide an array or a split on value.");
    }

    // Generate XY coordinate
    foreach ($data as $y => $row) {
        for ($x = 0; $x < strlen($row); $x++) {
            $coord = ($x + 1) . "," . ($y + 1);
            $coordinateSystem[$coord] = $row[$x];
        }
    }
    return $coordinateSystem;
}

// Function that gets all 8 surrounding characters of a coordinate including the coordinate itself
// It also returns the count of all the different values of the surrounding characters
function getSurroundingChars($coordinate, $coordinateSystem)
{
    $surroundingChars = [];
    $surroundingChars["TOP LEFT"] = getCharAt($coordinate, $coordinateSystem, "TOP LEFT");
    $surroundingChars["TOP"] = getCharAt($coordinate, $coordinateSystem, "TOP");
    $surroundingChars["TOP RIGHT"] = getCharAt($coordinate, $coordinateSystem, "TOP RIGHT");
    $surroundingChars["LEFT"] = getCharAt($coordinate, $coordinateSystem, "LEFT");
    $surroundingChars["MIDDLE"] = getCharAt($coordinate, $coordinateSystem);
    $surroundingChars["RIGHT"] = getCharAt($coordinate, $coordinateSystem, "RIGHT");
    $surroundingChars["BOTOTM LEFT"] = getCharAt($coordinate, $coordinateSystem, "BOTTOM LEFT");
    $surroundingChars["BOTTOM"] = getCharAt($coordinate, $coordinateSystem, "BOTTOM");
    $surroundingChars["BOTTOM RIGHT"] = getCharAt($coordinate, $coordinateSystem, "BOTTOM RIGHT");

    // Before returning, also count the values except the COUNT key itself and the "?" values
    $surroundingChars["COUNT"] = [];

    foreach ($surroundingChars as $key => $value) {
        if ($key != "COUNT" && $value != "?") {
            if (!isset($surroundingChars["COUNT"][$value])) {
                $surroundingChars["COUNT"][$value] = 1;
            } else {
                $surroundingChars["COUNT"][$value]++;
            }
        }
    }
    return $surroundingChars;
}

// Function to get the character at a specific coordinate, returns "?" if not found
function getCharAt($coordinate, $coordinateSystem, $relativePosition = "", $relativeOffset = 0)
{
    // If no "relative position" has been used then just try find the coordinate
    if ($relativePosition == "") {
        return isset($coordinateSystem[$coordinate]) ? $coordinateSystem[$coordinate] : "?";
    }

    // If a relative position has been used then check if that position exists based on current
    // provided position. TOP will check if the coordinate above the current coordinate exists.
    // BOTTOM will check if the coordinate below the current coordinate exists. LEFT will check
    // if the coordinate to the left of the current coordinate exists. RIGHT will check if the
    // TOP LEFT will check if the coordinate to the top left of the current coordinate exists.
    // TOP RIGHT will check if the coordinate to the top right of the current coordinate exists.
    // BOTTOM LEFT will check if the coordinate to the bottom left of the current coordinate exists.
    // BOTTOM RIGHT will check if the coordinate to the bottom right of the current coordinate exists.

    // Extract the X and Y coordinates from the current coordinate
    [$x, $y] = array_map('intval', explode(",", $coordinate));

    // Check if the relative position exists, and add the $relativeOffset to the current X or Y
    switch ($relativePosition) {
        case "TOP":
            return isset($coordinateSystem[$x . "," . ($y - 1 - $relativeOffset)]) ? $coordinateSystem[$x . "," . ($y - 1 - $relativeOffset)] : "?";
        case "BOTTOM":
            return isset($coordinateSystem[$x . "," . ($y + 1 + $relativeOffset)]) ? $coordinateSystem[$x . "," . ($y + 1 + $relativeOffset)] : "?";
        case "LEFT":
            return isset($coordinateSystem[($x - 1 - $relativeOffset) . "," . $y]) ? $coordinateSystem[($x - 1 - $relativeOffset) . "," . $y] : "?";
        case "RIGHT":
            return isset($coordinateSystem[($x + 1 + $relativeOffset) . "," . $y]) ? $coordinateSystem[($x + 1 + $relativeOffset) . "," . $y] : "?";
        case "TOP LEFT":
            return isset($coordinateSystem[($x - 1 - $relativeOffset) . "," . ($y - 1 - $relativeOffset)]) ? $coordinateSystem[($x - 1 - $relativeOffset) . "," . ($y - 1 - $relativeOffset)] : "?";
        case "TOP RIGHT":
            return isset($coordinateSystem[($x + 1 + $relativeOffset) . "," . ($y - 1 - $relativeOffset)]) ? $coordinateSystem[($x + 1 + $relativeOffset) . "," . ($y - 1 - $relativeOffset)] : "?";
        case "BOTTOM LEFT":
            return isset($coordinateSystem[($x - 1 - $relativeOffset) . "," . ($y + 1 + $relativeOffset)]) ? $coordinateSystem[($x - 1 - $relativeOffset) . "," . ($y + 1 + $relativeOffset)] : "?";
        case "BOTTOM RIGHT":
            return isset($coordinateSystem[($x + 1 + $relativeOffset) . "," . ($y + 1 + $relativeOffset)]) ? $coordinateSystem[($x + 1 + $relativeOffset) . "," . ($y + 1 + $relativeOffset)] : "?";
        default:
            return "?";
    }
}
