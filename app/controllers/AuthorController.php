<?php


class AuthorController extends BaseController {
   public function findauthors($book_id)
   {
        $authors = DB::table('authors')->join('books_authors', function($join) {
            $join->on('authors.author_id', '=', 'books_authors.author_id')->where('books_authors.book_id', '=', $book_id) })->get();

if ( isset($authors) && is_array($authors) ) {
     $str = '';
 foreach ( $authors as $author ) {
 $str .= "<div class='author_row'>";
 $str .= "<div class='author_fname'>".$author->fname."</div><div class='author_lname'>".$author->lname."</div>";
 $str .= "</div>";
 }
 echo json_encode( array('list' => $str, 'msg' => '') );
} else {
 echo json_encode( array('list' => null, 'msg' => 'Not Found') );
}
   }
}