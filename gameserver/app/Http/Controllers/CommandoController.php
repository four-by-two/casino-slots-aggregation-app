<?php

namespace App\Http\Controllers;

use App\Models\Commandos;
use Illuminate\Http\Request;

class CommandoController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth:api');
  }

  public function showAll()
  {
    $get = Commandos::count();
    if($get === 0) {
      $get = [
      [
        "id" => "link",
        "title" => "Link Commando Center",
        "author" => "Dejan",
        "content" => "Run this commando to to link with a dog instance. This can be operator level or aggregator level.",
      ],
      [
        "id" => "12345-12345-12345-12346",
        "title" => "Populate Commando's",
        "author" => "Dejan",
        "content" => "Run this commando to connect to dog instances.",
      ],
      ];
      return response()->json($get);
    }

    $formatted_articles = Commandos::all()->map(function ($item, $key) {
      $preview_array = explode(" ", $item["content"]);
      $preview_array_sliced = array_slice($preview_array, 0, 30);
      $preview_length = sizeof($preview_array);
      $item["content"] = join(" ", $preview_array_sliced) . ($preview_length > 30 ? "..." : "");
      return $item;
    });



    return response()->json($formatted_articles);
  }

  public function showOne($id)
  {
    return response()->json(Commandos::find($id));
  }

  public function create(Request $request)
  {
    $this->validate($request, [
      'title' => 'required',
      'author' => 'required',
      'content' => 'required',
    ]);
    $article = Commandos::create($request->all());

    return response()->json($article, 201);
  }

  public function update($id, Request $request)
  {
    $article = Commandos::findOrFail($id);
    $this->validate($request, [
      'title' => 'required',
      'author' => 'required',
      'content' => 'required',
    ]);
    $article->update($request->all());
    return response()->json($article, 200);
  }

  public function delete($id)
  {
    Commandos::findOrFail($id)->delete();
    return response('Deleted successfully', 200);
  }
}
