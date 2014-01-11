<?php

class BookController extends BaseController {
   public function findbook($keyword, $type)
   {
$search_type=array('title', 'isbn', 'author');
$ret = null;
if ( in_array($type, $search_type) ) {  
 $books = DB::table('books')->where($type, $keyword)->get();
 if ( isset($books) && is_array($books) ) {  
   $str = '';
   foreach ( $books as $book ) {
 $str .= "<div class='book_row'><div class='book_id' id='book_id_".$book->book_id."'>".$book->book_id."</div><div class='book_title'>".$book->title."</div>";
 $str .= "<div class='book_isbn'>".$book->isbn."</div><div class='book_desc'>".$book->description."</div></div>";
   }
   echo json_encode( array('list' => $str, 'msg' => '') );
 } else {
   echo json_encode( array('list' => null, 'msg' => 'Not Found') );
 }
        }
        echo json_encode( array('list' => null, 'msg' => 'Invalid Search Criteria!') );
   }
}