<?php

namespace App\Game\Kingdoms\Jobs;

use App\Admin\Mail\GenericMail;
use App\Flare\Events\ServerMessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Flare\Models\BuildingInQueue;
use App\Flare\Models\GameUnit;
use App\Flare\Models\Kingdom;
use App\Flare\Models\UnitInQueue;
use App\Flare\Transformers\KingdomTransformer;
use App\Game\Kingdoms\Events\UpdateKingdom;
use Facades\App\Flare\Values\UserOnlineValue;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Mail;

class RecruitUnits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Kingdom $kingdom
     */
    protected $kingdom;

    /**
     * @var GameUnit $unit
     */
    protected $unit;

    /**
     * @var int queueId
     */
    protected $queueId;

    /**
     * @var int $amount
     */
    protected $amount;

    /**
     * Create a new job instance.
     *
     * @param GameUnit $unit
     * @param Kingdom $kingdom
     * @param int $amount
     * @param int $queueId
     * @return void
     */
    public function __construct(GameUnit $unit, Kingdom $kingdom, int $amount, int $queueId)
    {
        $this->kingdom  = $kingdom;

        $this->unit     = $unit;

        $this->queueId  = $queueId;

        $this->amount   = $amount; 
    }

    /**
     * Execute the job.
     *
     * @param Manager $manager
     * @param KingdomTransformer $kingdomTransformer
     * @return void
     */
    public function handle(Manager $manager, KingdomTransformer $kingdomTransformer)
    {

        $queue = UnitInQueue::find($this->queueId);

        if (is_null($queue)) {
            return;
        }

        $amount = $this->amount;

        if ($this->kingdom->units->isEmpty()) {
            $this->kingdom->units()->create([
                'kingdom_id'   => $this->kingdom->id,
                'game_unit_id' => $this->unit->id,
                'amount'       => $amount,
            ]);
        } else {
            $found = $this->kingdom->units()->where('game_unit_id', $this->unit->id)->first();

            if (is_null($found)) {
                $this->kingdom->units()->create([
                    'kingdom_id'   => $this->kingdom->id,
                    'game_unit_id' => $this->unit->id,
                    'amount'       => $amount,
                ]);
            } else {
                $amount += $found->amount;

                $found->update([
                    'amount' => $amount,
                ]);
            }
        }

        $queue->delete();

        $kingdom     = $this->kingdom->refresh();
        $unitDetails = $kingdom->units()->where('game_unit_id', $this->unit->id)->first();
        $user        = $kingdom->character->user;
        
        if (UserOnlineValue::isOnline($user)) {
            $kingdom = new Item($kingdom, $kingdomTransformer);
            $kingdom = $manager->createData($kingdom)->toArray();

            event(new UpdateKingdom($user, $kingdom));
            event(new ServerMessageEvent($user, 'unit-recruitment-finished', $this->unit->name . ' finished recruiting for kingdom: ' . $this->kingdom->name . ' you have a total of: ' . $amount));
        } else {
            Mail::to($user)->send(new GenericMail(
                $user,
                $this->unit->name . ' finished recruiting for kingdom: ' . $this->kingdom->name . ' you have a total of: ' . $amount,
                'Unit Recruitment Finished: ' . $this->unit->name,
            ));
        }
    }
}