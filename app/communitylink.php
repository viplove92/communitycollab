<?php

namespace App;

use App\Exceptions\CommunityLinkAlreadySubmitted;
use Illuminate\Database\Eloquent\Model;

class communitylink extends Model
{
   protected $table = 'community_links';
   protected $fillable =
                      ['channel_id',
                       'title',
                       'link'
                      ];

   public static function from(User $user)
   {
      $link = new static;

      $link->user_id = $user->id;

      if ($user->istrusted()) {
          $link->approved();
      }

      return $link;

   }

   /**
    * Contirbute the given community link.
    * @param array $attributes
    * @return bool
    * @throws CommunityLinkAlreadySubmitted
    */
   public function contribute($attributes)
   {
      if($existing = $this->hasAlreadyBeenSubmitted($attributes['link'])){
        return $existing->touch();

         throw new CommunityLinkAlreadySubmitted;
      }
      return $this->fill($attributes)->save();
   }

   /**
    * Scope the query to record from perticular channel.
    *
    * @param Builder $builder
    * @param Channel $channel
    * @return Builder
    */
   public function scopeForChannel($builder, $channel)
   {
      if ($channel->exists){
          return $builder->where('channel_id', '$channel->id');
      }

      return $builder;
   }

   /**
    * mark the community link as approved.
    *
    * @return $this
    */
   public function approved()
   {
      $this->approved = true;

      return $this;
   }

   public function creator()
   {
      return $this->belongsTo(User::class, 'user_id');
   }

   public function channel()
   {
      return $this->belongsTo(channel::class);
   }

   /**
    * A community links may have many votes.
    *
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
   public function votes()
   {
      return $this->hasMany(CommunitylinkVote::class, 'community_link_id');
   }

   /**
    * Determine if the link has already been submitted
    * @param string $link
    * @return mixed
    */
   protected function hasAlreadyBeenSubmitted($link)
   {
      return static::where('link', $link)->first();
   }
}
