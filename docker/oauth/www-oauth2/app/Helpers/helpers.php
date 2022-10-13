<?php

function setting( $key )
{
	return \App\Setting::retrieve( $key );
}